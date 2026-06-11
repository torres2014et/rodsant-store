<?php

use App\Http\Controllers\Storefront\ComingSoonController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\Catalog;
use App\Livewire\Storefront\Checkout\CheckoutForm;
use App\Livewire\Storefront\Checkout\WhatsappOrderGenerator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda pública — RodSant Store
|--------------------------------------------------------------------------
| Home ya implementado. El resto de páginas (catálogo, producto, categoría,
| colección, cuenta, favoritos) se registran como rutas con nombre que de
| momento muestran un placeholder "Próximamente"; así el header, el footer
| y las tarjetas de producto enlazan a destinos válidos mientras se
| construyen esos módulos. Se irán reemplazando uno a uno.
*/

Route::get('/', HomeController::class)->name('home');

// Carrito y checkout (cierre por WhatsApp).
Route::get('/carrito', CartPage::class)->name('cart.index');
Route::get('/checkout', CheckoutForm::class)->name('checkout.index');
Route::get('/pedido/{order:order_number}', WhatsappOrderGenerator::class)->name('checkout.confirmation');

// Catálogo y derivados (catálogo con filtros Livewire + ficha de producto).
Route::get('/tienda', Catalog::class)->name('catalog.index');
Route::get('/novedades', Catalog::class)->name('catalog.new');
Route::get('/ofertas', Catalog::class)->name('catalog.sale');

Route::get('/categoria/{category:slug}', Catalog::class)->name('category.show');

Route::get('/producto/{product:slug}', ProductController::class)->name('product.show');

Route::get('/colecciones', fn () => app(ComingSoonController::class)('Colecciones'))->name('collections.index');
Route::get('/coleccion/{collection:slug}', Catalog::class)->name('collection.show');

// Cuenta y favoritos (placeholder; favoritos queda "preparado").
Route::get('/cuenta', fn () => app(ComingSoonController::class)('Mi cuenta'))->name('account.index');
Route::get('/favoritos', fn () => app(ComingSoonController::class)('Favoritos'))->name('wishlist.index');

// Páginas institucionales (placeholder).
Route::get('/pagina/{slug}', fn (string $slug) => app(ComingSoonController::class)(ucfirst(str_replace('-', ' ', $slug))))
    ->name('page.show');
