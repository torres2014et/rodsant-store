<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de la tienda RodSant Store
    |--------------------------------------------------------------------------
    |
    | Parámetros de negocio centralizados. Los valores sensibles o que cambian
    | por entorno se leen desde .env; el resto puede gestionarse también desde
    | la tabla `settings` a través del panel administrativo.
    |
    */

    'brand' => [
        'name' => 'RodSant Store',
        'short_name' => 'RodSant',
        'tagline' => 'Diseñado para moverse',
    ],

    /*
    | Canal de cierre de pedidos por WhatsApp.
    */
    'whatsapp' => [
        'number' => env('RODSANT_WHATSAPP_NUMBER', '573000000000'),
        'base_url' => 'https://wa.me/',
    ],

    /*
    | Moneda y formato monetario.
    */
    'currency' => [
        'code' => env('RODSANT_CURRENCY', 'COP'),
        'symbol' => env('RODSANT_CURRENCY_SYMBOL', '$'),
        'decimals' => 0,
    ],

    /*
    | Monedas de visualización para el selector flotante de la tienda.
    | La moneda base es COP; 'rate' es cuánto vale 1 COP en la moneda destino
    | (tasas aproximadas, solo para mostrar precios — el pedido se cierra en COP).
    */
    'currencies' => [
        'COP' => ['label' => 'Colombia', 'symbol' => '$', 'flag' => '🇨🇴', 'rate' => 1, 'decimals' => 0],
        'USD' => ['label' => 'Dólar', 'symbol' => 'US$', 'flag' => '🇺🇸', 'rate' => 0.00025, 'decimals' => 2],
        'EUR' => ['label' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺', 'rate' => 0.00023, 'decimals' => 2],
    ],

    /*
    | Reglas comerciales.
    */
    'orders' => [
        'prefix' => env('RODSANT_ORDER_PREFIX', 'RS'),
        'number_padding' => 6,
    ],

    'shipping' => [
        'free_from' => (int) env('RODSANT_FREE_SHIPPING_FROM', 250000),
    ],

    'inventory' => [
        'low_stock_threshold' => (int) env('RODSANT_LOW_STOCK_THRESHOLD', 3),
    ],

    'carts' => [
        'abandoned_after_hours' => (int) env('RODSANT_ABANDONED_CART_HOURS', 72),
    ],

    /*
    | Paginación por defecto del catálogo.
    */
    'catalog' => [
        'per_page' => 12,
    ],
];
