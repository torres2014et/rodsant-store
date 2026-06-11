<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'session_id',
        'coupon_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Subtotal del carrito (sin descuentos ni envío).
     */
    public function subtotal(): float
    {
        return (float) $this->items->sum(
            static fn (CartItem $item): float => (float) $item->unit_price * $item->quantity,
        );
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
