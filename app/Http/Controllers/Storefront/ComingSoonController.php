<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Página placeholder para secciones aún no construidas (catálogo, producto,
 * categoría, colección, cuenta…). Permite cablear toda la navegación del
 * header/footer/tarjetas con rutas con nombre válidas mientras se
 * desarrollan esos módulos. Reemplazar a medida que cada página exista.
 */
class ComingSoonController extends Controller
{
    public function __invoke(string $title = 'Próximamente'): View
    {
        return view('storefront.coming-soon', [
            'sectionTitle' => $title,
        ]);
    }
}
