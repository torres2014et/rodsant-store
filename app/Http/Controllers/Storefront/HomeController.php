<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Storefront\HomeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeService $home): View
    {
        return view('storefront.home', [
            'heroBanners' => $home->heroBanners(),
            'stripBanners' => $home->stripBanners(),
            'featuredCategories' => $home->featuredCategories(),
            'featuredProducts' => $home->featuredProducts(),
            'newProducts' => $home->newProducts(),
            'bestSellers' => $home->bestSellers(),
            'collections' => $home->collections(),
        ]);
    }
}
