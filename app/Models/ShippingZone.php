<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'department',
        'city',
        'cost',
        'free_from',
        'estimated_days',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'free_from' => 'decimal:2',
            'estimated_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Costo de envío para un subtotal dado (considera envío gratis por umbral).
     */
    public function costFor(float $subtotal): float
    {
        if ($this->free_from !== null && $subtotal >= (float) $this->free_from) {
            return 0.0;
        }

        return (float) $this->cost;
    }

    /**
     * @param  Builder<ShippingZone>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
