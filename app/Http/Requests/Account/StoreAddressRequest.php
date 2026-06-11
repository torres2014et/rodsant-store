<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'department' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'address_line' => ['required', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ];
    }
}
