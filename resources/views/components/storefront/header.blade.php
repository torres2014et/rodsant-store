@props([
    'transparent' => false,
])

@php
    // Enlaces principales del menú. Las categorías se inyectan vía View Composer.
    $primaryLinks = [
        ['label' => 'Novedades', 'route' => 'catalog.new'],
        ['label' => 'Colecciones', 'route' => 'collections.index'],
        ['label' => 'Ofertas', 'route' => 'catalog.sale'],
    ];
@endphp

<header
    x-data="{
        scrolled: false,
        mobileOpen: false,
        searchOpen: false,
        shopOpen: false,
    }"
    x-init="scrolled = window.scrollY > 8"
    @scroll.window="scrolled = window.scrollY > 8"
    @keydown.escape.window="mobileOpen = false; searchOpen = false; shopOpen = false"
    x-effect="document.body.style.overflow = (mobileOpen || searchOpen) ? 'hidden' : ''"
    class="sticky top-0 z-50"
>
    <div
        class="border-b transition-all duration-500"
        :class="scrolled
            ? 'bg-bone/95 backdrop-blur-md border-mist shadow-[0_1px_30px_-12px_rgba(10,10,10,0.25)]'
            : 'bg-bone border-transparent'"
        style="transition-timing-function: var(--ease-luxury);"
    >
        <div class="container-editorial">
            <div
                class="grid grid-cols-[1fr_auto_1fr] items-center transition-all duration-500"
                :class="scrolled ? 'h-14 lg:h-16' : 'h-16 lg:h-20'"
                style="transition-timing-function: var(--ease-luxury);"
            >

                {{-- Izquierda: navegación (desktop) / hamburguesa (móvil) --}}
                <div class="flex items-center">
                    <button
                        type="button"
                        @click="mobileOpen = true"
                        class="-ml-1 grid h-10 w-10 place-items-center text-noir lg:hidden"
                        aria-label="Abrir menú"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round" />
                        </svg>
                    </button>

                    <nav class="hidden items-center gap-8 lg:flex" aria-label="Principal">
                        {{-- Dropdown Tienda --}}
                        <div
                            class="relative"
                            @mouseenter="shopOpen = true"
                            @mouseleave="shopOpen = false"
                        >
                            <a href="{{ route('catalog.index') }}" class="nav-link flex items-center gap-1.5">
                                Tienda
                                <svg class="h-3 w-3 transition-transform duration-300" :class="shopOpen && 'rotate-180'"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>

                            @if ($navCategories->isNotEmpty())
                                <div
                                    x-show="shopOpen"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="absolute left-0 top-full w-60 pt-5"
                                >
                                    <div class="border border-mist bg-bone p-2 shadow-[0_24px_60px_-30px_rgba(10,10,10,0.4)]">
                                        @foreach ($navCategories as $category)
                                            <a href="{{ route('category.show', $category->slug) }}"
                                               class="flex items-center justify-between px-4 py-2.5 font-sans text-[0.8rem] uppercase tracking-[0.12em] text-noir/80 transition-colors hover:bg-bone/0 hover:text-noir">
                                                <span>{{ $category->name }}</span>
                                                <svg class="h-3 w-3 opacity-0 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @foreach ($primaryLinks as $link)
                            <a href="{{ route($link['route']) }}" class="nav-link">{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                </div>

                {{-- Centro: logo --}}
                <div class="flex justify-center">
                    <x-storefront.logo />
                </div>

                {{-- Derecha: acciones --}}
                <div class="flex items-center justify-end gap-1 sm:gap-2">
                    <button
                        type="button"
                        @click="searchOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                        class="grid h-10 w-10 place-items-center text-noir transition-opacity hover:opacity-60"
                        aria-label="Buscar"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.2-3.2" stroke-linecap="round" />
                        </svg>
                    </button>

                    <a href="{{ route('account.index') }}"
                       class="hidden h-10 w-10 place-items-center text-noir transition-opacity hover:opacity-60 sm:grid"
                       aria-label="Mi cuenta">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" />
                        </svg>
                    </a>

                    {{-- Favoritos (preparado) --}}
                    <a href="{{ route('wishlist.index') }}"
                       class="relative grid h-10 w-10 place-items-center text-noir transition-opacity hover:opacity-60"
                       aria-label="Favoritos">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 20s-7-4.4-9.3-9C1.2 8 2.6 4.8 5.8 4.8c1.9 0 3.3 1.1 4.2 2.4.9-1.3 2.3-2.4 4.2-2.4 3.2 0 4.6 3.2 3.1 6.2C19 15.6 12 20 12 20Z" stroke-linejoin="round" />
                        </svg>
                    </a>

                    {{-- Carrito (contador reactivo) --}}
                    <livewire:storefront.cart-counter />
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Overlay de búsqueda ===== --}}
    <div
        x-show="searchOpen"
        x-cloak
        @click.self="searchOpen = false"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-[60] bg-noir/40 backdrop-blur-sm"
    >
        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-bone"
            style="transition-timing-function: var(--ease-luxury);"
        >
            <div class="container-editorial py-6">
                <form action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-4">
                    <svg class="h-5 w-5 shrink-0 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.2-3.2" stroke-linecap="round" />
                    </svg>
                    <input
                        x-ref="searchInput"
                        type="search"
                        name="q"
                        placeholder="¿Qué estás buscando?"
                        class="w-full border-0 bg-transparent font-serif text-xl text-noir placeholder:text-ash focus:outline-none focus:ring-0 sm:text-2xl"
                        autocomplete="off"
                    >
                    <button type="button" @click="searchOpen = false"
                            class="shrink-0 font-sans text-[0.7rem] font-medium uppercase tracking-[0.18em] text-ash transition-colors hover:text-noir">
                        Cerrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Drawer de menú móvil ===== --}}
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-[70] lg:hidden">
        <div
            x-show="mobileOpen"
            x-transition.opacity.duration.300ms
            @click="mobileOpen = false"
            class="absolute inset-0 bg-noir/50 backdrop-blur-sm"
        ></div>

        <div
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[85%] max-w-sm flex-col bg-bone"
            style="transition-timing-function: var(--ease-luxury);"
        >
            <div class="flex items-center justify-between border-b border-mist px-6 py-5">
                <x-storefront.logo />
                <button type="button" @click="mobileOpen = false"
                        class="grid h-9 w-9 place-items-center text-noir" aria-label="Cerrar menú">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-6 py-8" aria-label="Menú móvil">
                <p class="eyebrow mb-4">Comprar</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('catalog.index') }}" class="block py-3 font-serif text-2xl text-noir">Tienda</a>
                    </li>
                    @foreach ($navCategories as $category)
                        <li>
                            <a href="{{ route('category.show', $category->slug) }}"
                               class="block py-2.5 font-sans text-[0.95rem] uppercase tracking-[0.1em] text-noir/70 transition-colors hover:text-noir">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="my-8 h-px bg-mist"></div>

                <ul class="space-y-1">
                    @foreach ($primaryLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}"
                               class="block py-3 font-serif text-2xl text-noir">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="border-t border-mist px-6 py-6">
                <div class="flex items-center gap-6">
                    <a href="{{ route('account.index') }}" class="flex items-center gap-2 text-sm text-noir/80 hover:text-noir">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4" /><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" />
                        </svg>
                        Mi cuenta
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="flex items-center gap-2 text-sm text-noir/80 hover:text-noir">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 20s-7-4.4-9.3-9C1.2 8 2.6 4.8 5.8 4.8c1.9 0 3.3 1.1 4.2 2.4.9-1.3 2.3-2.4 4.2-2.4 3.2 0 4.6 3.2 3.1 6.2C19 15.6 12 20 12 20Z" stroke-linejoin="round" />
                        </svg>
                        Favoritos
                    </a>
                </div>
                @if (! empty($socialLinks['instagram']))
                    <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener"
                       class="mt-5 inline-block font-sans text-[0.7rem] uppercase tracking-[0.2em] text-ash hover:text-noir">
                        {{ '@' . \Illuminate\Support\Str::after(rtrim($socialLinks['instagram'], '/'), 'instagram.com/') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
