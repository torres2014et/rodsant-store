<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkout;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El checkout está disponible para invitados y clientes autenticados.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'department' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'address_line' => ['required', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Necesitamos tu nombre para procesar el pedido.',
            'customer_phone.required' => 'El teléfono es obligatorio para contactarte por WhatsApp.',
            'department.required' => 'Indica el departamento de envío.',
            'city.required' => 'Indica la ciudad de envío.',
            'address_line.required' => 'Indica la dirección de envío.',
            'payment_method.required' => 'Selecciona un método de pago.',
        ];
    }
}
