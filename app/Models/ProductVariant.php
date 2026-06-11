<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockStatus;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'price_override',
        'barcode',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_product_variant');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * Precio efectivo de la variante (override o el del producto).
     */
    public function price(): string
    {
        return $this->price_override ?? $this->product->effectivePrice();
    }

    /**
     * Cantidad disponible (stock real menos reservado).
     */
    public function availableStock(): int
    {
        $inventory = $this->inventory;

        if ($inventory === null) {
            return 0;
        }

        return max(0, $inventory->quantity - $inventory->reserved);
    }

    public function stockStatus(): StockStatus
    {
        $threshold = $this->inventory?->low_stock_threshold
            ?? (int) config('rodsant.inventory.low_stock_threshold');

        return StockStatus::fromQuantity($this->availableStock(), $threshold);
    }

    /**
     * Etiqueta legible de la variante a partir de sus valores (ej. "Negro / M").
     */
    public function label(): string
    {
        return $this->attributeValues
            ->pluck('value')
            ->implode(' / ');
    }
}
