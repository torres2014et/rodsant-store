<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BannerType;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'image_desktop',
        'image_mobile',
        'link_url',
        'cta_label',
        'position',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BannerType::class,
            'position' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Banners activos y dentro de su ventana de vigencia.
     *
     * @param  Builder<Banner>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $now = Carbon::now();

        $query->where('is_active', true)
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('position');
    }

    /**
     * @param  Builder<Banner>  $query
     */
    public function scopeOfType(Builder $query, BannerType $type): void
    {
        $query->where('type', $type->value);
    }
}
