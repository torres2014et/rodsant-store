<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_total',
        'shipping_total',
        'grand_total',
        'coupon_id',
        'shipping_address_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'notes',
        'whatsapp_sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'whatsapp_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Accessor: total general formateado con la moneda configurada.
     */
    protected function grandTotalFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (): string => config('rodsant.currency.symbol')
                .number_format((float) $this->grand_total, (int) config('rodsant.currency.decimals'), ',', '.'),
        );
    }

    /**
     * Indica si el mensaje de WhatsApp ya fue generado para este pedido.
     */
    public function wasSentToWhatsapp(): bool
    {
        return $this->whatsapp_sent_at !== null;
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopeStatus(Builder $query, OrderStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * @param  Builder<Order>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', [
            OrderStatus::Delivered->value,
            OrderStatus::Cancelled->value,
        ]);
    }
}
