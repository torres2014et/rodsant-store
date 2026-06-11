@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
    'noindex' => false,
])

@php
    $brand = config('rodsant.brand.name', 'RodSant Store');
    $tagline = config('rodsant.brand.tagline', 'Street meets luxury');
    $metaTitle = $title ? "{$title} — {$brand}" : "{$brand} · {$tagline}";
    $metaDescription = $description
        ?? 'RodSant Store — ropa deportiva premium para mujer. Prendas técnicas para entrenar y moverte, con un estilo minimalista.';
    $canonicalUrl = $canonical ?? url()->current();
    $ogImage = $image ?? asset('favicon.svg');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0a">

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($noindex)
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow">
    @endif

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="es_CO">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Favicon / monograma RS --}}
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    {{-- Fuentes de marca --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Datos estructurados base: Organización + sitio --}}
    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => $brand,
                    'url' => url('/'),
                    'logo' => asset('favicon.svg'),
                    'slogan' => $tagline,
                    'sameAs' => array_values(array_filter((array) \App\Models\Setting::get('social_links', []))),
                ],
                [
                    '@type' => 'WebSite',
                    'name' => $brand,
                    'url' => url('/'),
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('structured-data')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen bg-bone text-noir antialiased selection:bg-noir selection:text-bone">
    {{-- Accesibilidad: salto al contenido --}}
    <a href="#contenido"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:bg-noir focus:px-4 focus:py-2 focus:text-bone">
        Saltar al contenido
    </a>

    <x-storefront.announcement-bar />
    <x-storefront.header />

    <main id="contenido">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    {{-- Selector de moneda flotante (abajo a la izquierda) --}}
    <livewire:storefront.currency-switcher />

    {{-- Botón flotante de WhatsApp (abajo a la derecha) --}}
    <x-storefront.whatsapp-float />

    @livewireScripts
    @stack('scripts')
</body>
</html>
