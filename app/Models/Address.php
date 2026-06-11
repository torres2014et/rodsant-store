<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'full_name',
        'phone',
        'department',
        'city',
        'address_line',
        'references',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Representación de una línea para el mensaje de WhatsApp o el panel.
     */
    public function summary(): string
    {
        return trim("{$this->address_line}, {$this->city}, {$this->department}");
    }
}
