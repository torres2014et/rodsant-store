<?php

declare(strict_types=1);

namespace App\Services\Storefront;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Consultas y filtros del catálogo público.
 *
 * Centraliza la lógica de listado/filtrado de productos (convención
 * MVC + capa de servicios) para que el componente Livewire solo orqueste.
 */
class CatalogService
{
    /**
     * Aplica filtros y devuelve productos paginados, listos para tarjeta.
     *
     * @param  array{
     *     category_id?: int|null,
     *     collection_id?: int|null,
     *     only_new?: bool,
     *     only_sale?: bool,
     *     colors?: list<string>,
     *     sizes?: list<string>,
     *     search?: string|null,
     *     sort?: string,
     *     per_page?: int
     * }  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['collection_id'])) {
            $collectionId = $filters['collection_id'];
            $query->whereHas('collections', fn (Builder $q) => $q->where('collections.id', $collectionId));
        }

        if (! empty($filters['only_new'])) {
            $query->where('is_new', true);
        }

        if (! empty($filters['only_sale'])) {
            $query->onSale();
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['colors'])) {
            $colors = $filters['colors'];
            $query->whereHas('variants.attributeValues', fn (Builder $q) => $q->whereIn('value', $colors));
        }

        if (! empty($filters['sizes'])) {
            $sizes = $filters['sizes'];
            $query->whereHas('variants.attributeValues', fn (Builder $q) => $q->whereIn('value', $sizes));
        }

        $this->applySort($query, $filters['sort'] ?? 'relevancia');

        return $query->paginate($filters['per_page'] ?? 12)->withQueryString();
    }

    /**
     * Categorías activas con conteo de productos visibles (para filtros).
     *
     * @return EloquentCollection<int, Category>
     */
    public function categories(): EloquentCollection
    {
        return Category::query()
            ->active()
            ->roots()
            ->withCount(['products' => fn (Builder $q) => $q->where('is_active', true)])
            ->orderBy('position')
            ->get();
    }

    /**
     * Valores de un atributo (talla/color) para los chips de filtro.
     *
     * @return list<array{value: string, hex: string|null}>
     */
    public function attributeValues(string $slug): array
    {
        $attribute = Attribute::query()->where('slug', $slug)->first();

        if ($attribute === null) {
            return [];
        }

        return $attribute->values()
            ->orderBy('position')
            ->get()
            ->map(fn ($value): array => [
                'value' => $value->value,
                'hex' => $value->hex(),
            ])
            ->all();
    }

    /**
     * Productos relacionados (misma categoría) para la ficha de producto.
     *
     * @return EloquentCollection<int, Product>
     */
    public function related(Product $product, int $limit = 4): EloquentCollection
    {
        return $this->baseQuery()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Consulta base reutilizable: solo activos, con todo lo que necesita
     * la tarjeta de producto (imagen, categoría, variantes/colores/stock).
     *
     * @return Builder<Product>
     */
    private function baseQuery(): Builder
    {
        return Product::query()
            ->active()
            ->with([
                'primaryImage',
                'images:id,product_id,path,alt,position,is_primary',
                'category:id,name,slug',
                'variants.inventory',
                'variants.attributeValues.attribute:id,slug',
            ]);
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'precio_asc' => $query->orderByRaw('COALESCE(sale_price, base_price) asc'),
            'precio_desc' => $query->orderByRaw('COALESCE(sale_price, base_price) desc'),
            'nuevos' => $query->latest(),
            'nombre' => $query->orderBy('name'),
            default => $query->orderByDesc('is_featured')->orderBy('position')->latest(),
        };
    }
}
