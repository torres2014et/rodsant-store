<?php

declare(strict_types=1);

namespace App\Services\Storefront;

use App\Enums\BannerType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

/**
 * Reúne y prepara los datos del Home de la tienda.
 *
 * Mantiene la lógica de consulta fuera de los controladores/vistas
 * (convención MVC + capa de servicios de RodSant).
 */
class HomeService
{
    /**
     * Banner(s) principales activos para el hero.
     *
     * @return EloquentCollection<int, Banner>
     */
    public function heroBanners(): EloquentCollection
    {
        return Banner::query()
            ->visible()
            ->ofType(BannerType::Hero)
            ->get();
    }

    /**
     * Bandas intermedias (promos editoriales) activas.
     *
     * @return EloquentCollection<int, Banner>
     */
    public function stripBanners(): EloquentCollection
    {
        return Banner::query()
            ->visible()
            ->ofType(BannerType::Strip)
            ->get();
    }

    /**
     * Categorías destacadas para la cuadrícula del Home.
     *
     * @return EloquentCollection<int, Category>
     */
    public function featuredCategories(int $limit = 6): EloquentCollection
    {
        return Category::query()
            ->active()
            ->roots()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('position')
            ->take($limit)
            ->get();
    }

    /**
     * Productos destacados (marcados como is_featured).
     *
     * @return EloquentCollection<int, Product>
     */
    public function featuredProducts(int $limit = 8): EloquentCollection
    {
        return $this->baseProductQuery()
            ->where('is_featured', true)
            ->orderBy('position')
            ->take($limit)
            ->get();
    }

    /**
     * Novedades (productos marcados como nuevos, más recientes primero).
     *
     * @return EloquentCollection<int, Product>
     */
    public function newProducts(int $limit = 8): EloquentCollection
    {
        return $this->baseProductQuery()
            ->where('is_new', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Más vendidos: por unidades vendidas reales; si no hay ventas,
     * cae a los más vistos para que el Home nunca quede vacío.
     *
     * @return EloquentCollection<int, Product>
     */
    public function bestSellers(int $limit = 8): EloquentCollection
    {
        $rankedIds = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->select('product_variants.product_id')
            ->selectRaw('SUM(order_items.quantity) as units')
            ->groupBy('product_variants.product_id')
            ->orderByDesc('units')
            ->limit($limit)
            ->pluck('product_variants.product_id')
            ->map(static fn ($id): int => (int) $id);

        if ($rankedIds->isEmpty()) {
            return $this->baseProductQuery()
                ->orderByDesc('views')
                ->take($limit)
                ->get();
        }

        $products = $this->baseProductQuery()
            ->whereIn('id', $rankedIds->all())
            ->get()
            ->keyBy('id');

        // Conservar el orden por ranking de ventas.
        return $rankedIds
            ->map(fn (int $id) => $products->get($id))
            ->filter()
            ->values()
            ->pipe(fn ($items) => new EloquentCollection($items->all()));
    }

    /**
     * Colecciones activas con portada para el bloque "Colecciones".
     *
     * @return EloquentCollection<int, Collection>
     */
    public function collections(int $limit = 3): EloquentCollection
    {
        return Collection::query()
            ->active()
            ->withCount('products')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Consulta base reutilizable de productos para tarjetas:
     * solo activos, con imagen principal, categoría y variantes/colores.
     *
     * @return Builder<Product>
     */
    private function baseProductQuery()
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
}
