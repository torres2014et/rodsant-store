<?php

declare(strict_types=1);

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CartException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Convierte un carrito en un pedido real de forma atómica.
 *
 * Toda la operación corre dentro de una transacción con bloqueo de filas de
 * inventario (lockForUpdate) para impedir sobreventa ante peticiones
 * concurrentes. Si algo falla, no se crea nada (rollback total).
 */
class OrderService
{
    /**
     * Crea el pedido a partir del carrito y los datos del checkout.
     *
     * @param  array{full_name:string, phone:string, email:?string, department:string, city:string, address_line:string, references:?string, notes:?string}  $data
     *
     * @throws CartException
     */
    public function placeFromCart(Cart $cart, array $data): Order
    {
        return DB::transaction(function () use ($cart, $data): Order {
            $cart->load([
                'items.variant.product',
                'items.variant.attributeValues.attribute',
            ]);

            if ($cart->items->isEmpty()) {
                throw CartException::emptyCart();
            }

            // 1) Bloquear inventario y validar disponibilidad de cada línea.
            $prepared = [];
            $subtotal = 0.0;

            foreach ($cart->items as $item) {
                $variant = $item->variant;

                if ($variant === null || ! $variant->is_active || ! ($variant->product?->is_active)) {
                    throw CartException::unavailable($variant?->product?->name ?? 'Producto');
                }

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $available = $inventory !== null ? max(0, $inventory->quantity - $inventory->reserved) : 0;
                $name = trim($variant->product->name.' '.$variant->label());

                if ($available <= 0) {
                    throw CartException::outOfStock($name);
                }

                if ($item->quantity > $available) {
                    throw CartException::insufficientStock($name, $available);
                }

                $unitPrice = (float) $item->unit_price;
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                $prepared[] = [
                    'variant' => $variant,
                    'inventory' => $inventory,
                    'product_name' => $variant->product->name,
                    'variant_label' => $variant->label() ?: null,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ];
            }

            // 2) Cliente (invitado) + dirección de envío.
            $customer = $this->resolveCustomer($data);
            $address = $this->createAddress($customer, $data);

            // 3) Cabecera del pedido (estado inicial: pendiente).
            $order = $this->createOrder($cart, $customer, $address, $data, $subtotal);

            // 4) Líneas con snapshot inmutable + reserva de inventario.
            foreach ($prepared as $row) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $row['variant']->id,
                    'product_name' => $row['product_name'],
                    'variant_label' => $row['variant_label'],
                    'unit_price' => $row['unit_price'],
                    'quantity' => $row['quantity'],
                    'line_total' => $row['line_total'],
                ]);

                $row['inventory']?->increment('reserved', $row['quantity']);
            }

            // 5) Vaciar el carrito ya convertido.
            $cart->items()->delete();

            return $order->load('items', 'shippingAddress');
        });
    }

    /**
     * Registra que el pedido ya se trasladó a WhatsApp (idempotente).
     */
    public function markWhatsappSent(Order $order): void
    {
        if ($order->whatsapp_sent_at === null) {
            $order->forceFill(['whatsapp_sent_at' => now()])->save();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveCustomer(array $data): Customer
    {
        $email = $data['email'] ?? null;

        $customer = null;
        if (! empty($email)) {
            $customer = Customer::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->first();
        }

        if ($customer === null) {
            $customer = new Customer;
        }

        $customer->fill([
            'full_name' => $data['full_name'],
            'email' => $email,
            'phone' => $data['phone'],
        ])->save();

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createAddress(Customer $customer, array $data): Address
    {
        return Address::query()->create([
            'customer_id' => $customer->id,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'department' => $data['department'],
            'city' => $data['city'],
            'address_line' => $data['address_line'],
            'references' => $data['references'] ?? null,
            'is_default' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOrder(Cart $cart, Customer $customer, Address $address, array $data, float $subtotal): Order
    {
        $attributes = [
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'payment_method' => PaymentMethod::Whatsapp,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'grand_total' => $subtotal,
            'shipping_address_id' => $address->id,
            'customer_name' => $data['full_name'],
            'customer_phone' => $data['phone'],
            'customer_email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        // Número único con reintento ante colisión del índice único.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return Order::query()->create(
                    ['order_number' => $this->nextOrderNumber()] + $attributes
                );
            } catch (QueryException $e) {
                if (! $this->isDuplicate($e) || $attempt === 4) {
                    throw $e;
                }
            }
        }

        // Inalcanzable, pero satisface el analizador estático.
        throw new \RuntimeException('No se pudo generar el número de pedido.');
    }

    /**
     * Genera el siguiente número con formato PREFIJO-AÑO-NNNNNN (ej. RS-2026-000123).
     */
    private function nextOrderNumber(): string
    {
        $prefix = (string) config('rodsant.orders.prefix', 'RS');
        $padding = (int) config('rodsant.orders.number_padding', 6);
        $year = (int) now()->year;

        $sequence = Order::withTrashed()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%s', $prefix, $year, str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT));
    }

    private function isDuplicate(QueryException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062 // MySQL duplicate entry
            || str_contains(mb_strtolower($e->getMessage()), 'unique');
    }
}
