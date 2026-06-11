<?php

declare(strict_types=1);

namespace App\Livewire\Storefront\Checkout;

use App\Exceptions\CartException;
use App\Services\Cart\CartService;
use App\Services\Checkout\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront', ['title' => 'Finalizar compra'])]
class CheckoutForm extends Component
{
    // Datos de contacto
    public string $full_name = '';

    public string $phone = '';

    public string $email = '';

    // Datos de envío
    public string $department = '';

    public string $city = '';

    public string $address_line = '';

    public string $references = '';

    public string $notes = '';

    public ?string $stockError = null;

    /**
     * Redirige al carrito si está vacío (no se puede pagar sin productos).
     */
    public function mount(CartService $cart): mixed
    {
        if ($cart->isEmpty()) {
            return $this->redirect(route('cart.index'), navigate: true);
        }

        return null;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'min:7', 'max:30', 'regex:/^[0-9+\s().-]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:160'],
            'department' => ['required', 'string', 'min:3', 'max:80'],
            'city' => ['required', 'string', 'min:3', 'max:80'],
            'address_line' => ['required', 'string', 'min:5', 'max:180'],
            'references' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'full_name.required' => 'Cuéntanos a nombre de quién va el pedido.',
            'full_name.min' => 'Escribe el nombre completo.',
            'phone.required' => 'Necesitamos un teléfono para coordinar la entrega.',
            'phone.regex' => 'El teléfono solo puede contener números.',
            'phone.min' => 'El teléfono parece incompleto.',
            'email.email' => 'Revisa el correo electrónico.',
            'department.required' => 'Indica el departamento.',
            'city.required' => 'Indica la ciudad.',
            'address_line.required' => 'Indica la dirección de entrega.',
            'address_line.min' => 'La dirección parece muy corta.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'full_name' => 'nombre completo',
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
            'department' => 'departamento',
            'city' => 'ciudad',
            'address_line' => 'dirección',
            'references' => 'referencias',
            'notes' => 'notas',
        ];
    }

    public function placeOrder(CartService $cart, OrderService $orders): mixed
    {
        $data = $this->validate();

        $current = $cart->currentOrNull();

        if ($current === null || $current->items->isEmpty()) {
            return $this->redirect(route('cart.index'), navigate: true);
        }

        // Defensa adicional antes de tocar inventario.
        $problems = $cart->validateAgainstStock($current);
        if ($problems !== []) {
            $this->stockError = $problems[0];
            $this->dispatch('cart-updated');

            return null;
        }

        try {
            $order = $orders->placeFromCart($current, [
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?: null,
                'department' => $data['department'],
                'city' => $data['city'],
                'address_line' => $data['address_line'],
                'references' => $data['references'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);
        } catch (CartException $e) {
            $this->stockError = $e->getMessage();
            $this->dispatch('cart-updated');

            return null;
        }

        // Token de acceso a la confirmación para esta sesión.
        session()->put('rodsant_last_order', $order->id);
        $this->dispatch('cart-updated');

        return $this->redirect(route('checkout.confirmation', $order->order_number), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.storefront.checkout.checkout-form');
    }
}
