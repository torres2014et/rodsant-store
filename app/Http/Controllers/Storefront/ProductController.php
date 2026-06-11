<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\CatalogService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __invoke(Product $product, CatalogService $catalog): View
    {
        if (! $product->is_active) {
            throw new NotFoundHttpException;
        }

        $product->loadMissing([
            'category:id,name,slug',
            'images',
            'variants.inventory',
            'variants.attributeValues.attribute:id,slug',
        ]);

        // Contador de vistas (no crítico).
        $product->increment('views');

        return view('storefront.product', [
            'product' => $product,
            'related' => $catalog->related($product),
        ]);
    }
}
