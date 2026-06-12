@php
    $storeName = $store['name'] ?? config('rodsant.brand.name');
    $storeTagline = $store['tagline'] ?? config('rodsant.brand.tagline');
    $storeEmail = $store['email'] ?? null;
    $storeAddress = $store['address'] ?? null;
    $instagram = $socialLinks['instagram'] ?? null;
    $tiktok = $socialLinks['tiktok'] ?? null;
    $facebook = $socialLinks['facebook'] ?? null;
    $whatsapp = $whatsappNumber ?? null;

    $policies = [
        ['label' => 'Envíos y entregas', 'slug' => 'envios'],
        ['label' => 'Cambios y devoluciones', 'slug' => 'devoluciones'],
        ['label' => 'Guía de tallas', 'slug' => 'guia-de-tallas'],
        ['label' => 'Términos y condiciones', 'slug' => 'terminos'],
        ['label' => 'Política de privacidad', 'slug' => 'privacidad'],
    ];
@endphp

<footer class="bg-noir/90 text-bone">
    <div class="container-editorial py-16 lg:py-24">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-8">

            {{-- Marca --}}
            <div class="lg:col-span-4">
                <x-storefront.logo :light="true" />
                <p class="mt-6 max-w-xs font-sans text-base font-light leading-relaxed text-bone/70">
                    {{ $storeTagline }}. Ropa deportiva premium para mujer: prendas técnicas
                    pensadas para el entrenamiento y el movimiento.
                </p>

                <div class="mt-8 flex items-center gap-4">
                    @if ($instagram)
                        <a href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram"
                           class="grid h-10 w-10 place-items-center border border-bone/20 text-bone/80 transition-colors hover:border-bone hover:text-bone">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17.5" cy="6.5" r="0.6" fill="currentColor" />
                            </svg>
                        </a>
                    @endif
                    @if ($tiktok)
                        <a href="{{ $tiktok }}" target="_blank" rel="noopener" aria-label="TikTok"
                           class="grid h-10 w-10 place-items-center border border-bone/20 text-bone/80 transition-colors hover:border-bone hover:text-bone">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16.5 3c.3 2.1 1.6 3.6 3.5 3.8V9c-1.3 0-2.5-.4-3.5-1.1v6.3a5.2 5.2 0 1 1-5.2-5.2c.3 0 .5 0 .8.1v2.4a2.8 2.8 0 1 0 2 2.7V3h2.4Z" />
                            </svg>
                        </a>
                    @endif
                    @if ($facebook)
                        <a href="{{ $facebook }}" target="_blank" rel="noopener" aria-label="Facebook"
                           class="grid h-10 w-10 place-items-center border border-bone/20 text-bone/80 transition-colors hover:border-bone hover:text-bone">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13.5 21v-7h2.3l.4-2.7h-2.7V9.5c0-.8.2-1.3 1.4-1.3h1.4V5.8c-.7-.1-1.4-.1-2.1-.1-2.1 0-3.5 1.3-3.5 3.6v2H8.3V14h2.3v7h2.9Z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Comprar --}}
            <div class="lg:col-span-2">
                <h3 class="font-sans text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-bone/50">Comprar</h3>
                <ul class="mt-6 space-y-3">
                    <li><a href="{{ route('catalog.index') }}" class="text-sm text-bone/75 transition-colors hover:text-bone">Tienda completa</a></li>
                    @foreach ($navCategories->take(5) as $category)
                        <li>
                            <a href="{{ route('category.show', $category->slug) }}"
                               class="text-sm text-bone/75 transition-colors hover:text-bone">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Ayuda --}}
            <div class="lg:col-span-3">
                <h3 class="font-sans text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-bone/50">Ayuda</h3>
                <ul class="mt-6 space-y-3">
                    @foreach ($policies as $policy)
                        <li>
                            <a href="{{ route('page.show', $policy['slug']) }}"
                               class="text-sm text-bone/75 transition-colors hover:text-bone">{{ $policy['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contacto --}}
            <div class="lg:col-span-3">
                <h3 class="font-sans text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-bone/50">Contacto</h3>
                <ul class="mt-6 space-y-4 text-sm text-bone/75">
                    @if ($whatsapp)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-bone/60" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2Zm5.2 13.9c-.2.6-1.2 1.2-1.7 1.2-.4 0-1 .1-3.4-.9-2.9-1.2-4.7-4.2-4.8-4.4-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 .9-2.2.2-.2.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .6l-.4.6c-.1.2-.3.3-.1.6.5.8 1 1.4 1.7 1.9.6.4 1 .6 1.3.7.2.1.4.1.5-.1l.7-.8c.2-.2.3-.2.6-.1l1.9.9c.3.1.5.2.5.4.1.2.1.9-.1 1.5Z" />
                            </svg>
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                               class="transition-colors hover:text-bone">WhatsApp</a>
                        </li>
                    @endif
                    @if ($storeEmail)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-bone/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                            <a href="mailto:{{ $storeEmail }}" class="transition-colors hover:text-bone">{{ $storeEmail }}</a>
                        </li>
                    @endif
                    @if ($storeAddress)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-bone/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                            <span>{{ $storeAddress }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Barra inferior --}}
    <div class="border-t border-bone/10">
        <div class="container-editorial flex flex-col items-center justify-between gap-4 py-6 sm:flex-row">
            <p class="font-sans text-xs tracking-wide text-bone/50">
                © {{ now()->year }} {{ $storeName }}. Todos los derechos reservados.
            </p>
            <p class="font-sans text-[0.65rem] uppercase tracking-[0.2em] text-bone/40">
                Diseñado en Colombia · Hecho para moverse
            </p>
        </div>
    </div>
</footer>
