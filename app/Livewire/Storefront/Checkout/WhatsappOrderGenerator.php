<?php

declare(strict_types=1);

namespace App\Livewire\Storefront\Checkout;

use App\Models\Order;
use App\Services\Checkout\OrderService;
use App\Services\Checkout\WhatsappMessageService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Pantalla de "pedido creado": muestra el número, el resumen y genera/abre
 * el mensaje de WhatsApp hacia la tienda. Marca whatsapp_sent_at al enviarse.
 */
#[Layout('components.layouts.storefront', ['title' => 'Pedido confirmado'])]
class WhatsappOrderGenerator extends Component
{
    public int $orderId;

    public string $orderNumber;

    public string $customerName;

    public string $whatsappUrl;

    /** @var list<array{name:string, label:?string, quantity:int, line_total:float}> */
    public array $items = [];

    public float $subtotal = 0.0;

    public float $total = 0.0;

    public ?string $addressCity = null;

    public ?string $addressLine = null;

    public ?string $addressReferences = null;

    public ?string $notes = null;

    public bool $sent = false;

    public function mount(Order $order, WhatsappMessageService $whatsapp): void
    {
        $order->loadMissing(['items', 'shippingAddress']);

        $this->orderId = $order->id;
        $this->orderNumber = $order->order_number;
        $this->customerName = $order->customer_name;
        $this->subtotal = (float) $order->subtotal;
        $this->total = (float) $order->grand_total;
        $this->notes = $order->notes;
        $this->sent = $order->wasSentToWhatsapp();
        $this->whatsappUrl = $whatsapp->url($order);

        if ($order->shippingAddress !== null) {
            $this->addressCity = trim($order->shippingAddress->city.', '.$order->shippingAddress->department);
            $this->addressLine = $order->shippingAddress->address_line;
            $this->addressReferences = $order->shippingAddress->references;
        }

        $this->items = $order->items
            ->map(fn ($item): array => [
                'name' => $item->product_name,
                'label' => $item->variant_label,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ])
            ->all();
    }

    /**
     * Marca el pedido como trasladado a WhatsApp (idempotente).
     */
    public function markSent(OrderService $orders): void
    {
        $order = Order::find($this->orderId);

        if ($order !== null) {
            $orders->markWhatsappSent($order);
            $this->sent = true;
        }
    }

    public function render(): View
    {
        return view('livewire.storefront.checkout.whatsapp-order-generator');
    }
}
