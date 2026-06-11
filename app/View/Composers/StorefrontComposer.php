<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Inyecta los datos compartidos por header, footer y barra de anuncio:
 * categorías de navegación y ajustes de la tienda (marca, redes, contacto).
 */
class StorefrontComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'navCategories' => $this->navCategories(),
            'store' => Setting::get('store', [
                'name' => config('rodsant.brand.name'),
                'tagline' => config('rodsant.brand.tagline'),
                'email' => null,
                'address' => null,
            ]),
            'socialLinks' => array_filter(Setting::get('social_links', [])),
            'whatsappNumber' => Setting::get('whatsapp_number', config('rodsant.whatsapp.number')),
        ]);
    }

    /**
     * Categorías raíz activas para el menú principal (cacheadas 1h).
     *
     * @return Collection<int, Category>
     */
    private function navCategories()
    {
        return Cache::remember('storefront:nav-categories', now()->addHour(), function () {
            return Category::query()
                ->active()
                ->roots()
                ->with(['children' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'parent_id']);
        });
    }
}
