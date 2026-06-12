@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $resolveImg = static fn (?string $path): ?string => $path
        ? (Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path))
        : null;

    $hero = $heroBanners->first();
    $instagram = $socialLinks['instagram'] ?? null;
    $instagramHandle = $instagram ? Str::after(rtrim($instagram, '/'), 'instagram.com/') : 'rodsantstore';
@endphp

<x-layouts.storefront
    description="RodSant Store — ropa deportiva premium para mujer. Tops, leggings, conjuntos y outerwear técnicos diseñados para entrenar y moverte."
>
    {{-- ============================ HERO ============================ --}}
    @php
        $heroImg = ($hero && ($img = $resolveImg($hero->image_desktop)))
            ? $img
            : asset('img/hero-storefront.jpg');
    @endphp
    <section class="bg-noir text-bone">
        {{-- Foto arriba: completa y nítida, con relleno difuminado en los lados (se nota en PC) --}}
        <div class="relative overflow-hidden bg-noir">
            {{-- Fondo: logo de la marca, ampliado, difuminado y oscurecido — rellena los lados --}}
            <img
                src="{{ asset('img/rodsant-logo.jpg') }}"
                alt=""
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-xl"
            >
            <div class="absolute inset-0 bg-noir/40"></div>

            {{-- Foto nítida, completa, centrada --}}
            <img
                src="{{ $heroImg }}"
                alt="{{ $hero?->title ?? 'Fachada del local de RodSant Store' }}"
                class="relative mx-auto block max-h-[80vh] w-auto max-w-full"
                fetchpriority="high"
            >
        </div>

        {{-- Bloque de texto debajo --}}
        <div class="container-editorial py-16 lg:py-20">
            <div class="max-w-2xl">
                <p class="eyebrow text-bone/60">{{ $hero?->subtitle ?? config('rodsant.brand.tagline') }}</p>

                <h1 class="mt-6 font-display text-5xl font-extrabold uppercase leading-[0.92] tracking-tight text-bone sm:text-7xl lg:text-[5.25rem]">
                    {!! $hero?->title
                        ? e($hero->title)
                        : 'Muévete<br>sin límites' !!}
                </h1>

                <p class="mt-8 max-w-md text-lg font-light leading-relaxed text-bone/70">
                    Ropa deportiva diseñada para la mujer activa: del entrenamiento al día a día.
                    Comodidad, soporte y libertad de movimiento en cada prenda.
                </p>

                <div class="mt-12 flex flex-wrap items-center gap-4">
                    <a href="{{ $hero?->link_url ?? route('catalog.index') }}" class="btn-bone">
                        {{ $hero?->cta_label ?? 'Explorar la tienda' }}
                    </a>
                    <a href="{{ route('catalog.new') }}"
                       class="inline-flex items-center gap-2 border border-bone/30 px-8 py-4 font-sans text-[0.72rem] font-semibold uppercase tracking-[0.22em] text-bone transition-colors duration-500 hover:bg-bone hover:text-noir">
                        Ver novedades
                    </a>
                </div>
            </div>
        </div>

    </section>

    {{-- ===================== CATEGORÍAS DESTACADAS ===================== --}}
    @if ($featuredCategories->isNotEmpty())
        <section class="bg-bone py-16 lg:py-24">
            <div class="container-editorial">
                <x-storefront.section-header
                    class="reveal"
                    eyebrow="Equípate"
                    title="Compra por categoría"
                    :href="route('catalog.index')"
                    link-label="Ver todas"
                />

                <div class="reveal mt-10 grid grid-cols-2 gap-4 sm:gap-6 lg:mt-14 lg:grid-cols-3 lg:gap-8">
                    @foreach ($featuredCategories as $category)
                        @php $catImg = $resolveImg($category->image); @endphp
                        <a href="{{ route('category.show', $category->slug) }}"
                           class="group relative block aspect-[4/5] overflow-hidden bg-mist lg:aspect-[3/4]">
                            @if ($catImg)
                                <img src="{{ $catImg }}" alt="{{ $category->name }}"
                                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1.2s] group-hover:scale-105"
                                     style="transition-timing-function: var(--ease-luxury);" loading="lazy">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-ink to-noir">
                                    <span class="font-display text-6xl font-semibold text-bone/10">RS</span>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-noir/70 via-transparent to-transparent"></div>

                            <div class="absolute inset-x-0 bottom-0 p-5 lg:p-7">
                                <h3 class="font-serif text-xl font-medium text-bone lg:text-2xl">{{ $category->name }}</h3>
                                @if (isset($category->products_count))
                                    <p class="mt-1 font-sans text-[0.65rem] uppercase tracking-[0.2em] text-bone/60">
                                        {{ $category->products_count }} {{ Str::plural('prenda', $category->products_count) }}
                                    </p>
                                @endif
                                <span class="mt-3 inline-flex items-center gap-1.5 font-sans text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-bone opacity-0 transition-all duration-500 group-hover:opacity-100">
                                    Descubrir
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== PRODUCTOS DESTACADOS ===================== --}}
    <x-storefront.product-collection
        eyebrow="Selección"
        title="Esenciales de entrenamiento"
        subtitle="Las prendas que toda mujer necesita en su rutina."
        :products="$featuredProducts"
        :href="route('catalog.index')"
    />

    {{-- ===================== BANDA EDITORIAL ===================== --}}
    <section class="relative overflow-hidden bg-ink text-bone">
        <span class="pointer-events-none absolute -bottom-16 -left-10 select-none font-display text-[16rem] font-bold leading-none text-bone/[0.03]">RS</span>
        <div class="container-editorial relative grid items-center gap-10 py-20 lg:grid-cols-2 lg:py-28">
            <div class="reveal">
                <p class="eyebrow text-bone/50">Mujer RodSant</p>
                <h2 class="mt-5 font-display text-4xl font-extrabold uppercase leading-[0.95] tracking-tight text-bone sm:text-5xl">
                    Hecho<br>para ti.
                </h2>
            </div>
            <div class="reveal">
                <p class="text-lg font-light leading-relaxed text-bone/70">
                    Prendas deportivas que combinan comodidad, soporte y estilo.
                    Diseñadas para acompañarte del entrenamiento al día a día, siempre con actitud.
                </p>
                <a href="{{ route('collections.index') }}"
                   class="mt-8 inline-flex items-center gap-2 border-b border-bone/40 pb-1 font-sans text-[0.72rem] font-semibold uppercase tracking-[0.2em] text-bone transition-colors hover:border-bone">
                    Conoce las colecciones
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ===================== NOVEDADES ===================== --}}
    <x-storefront.product-collection
        eyebrow="Recién llegado"
        title="Novedades"
        subtitle="Lo último para que entrenes con estilo y actitud."
        :products="$newProducts"
        :href="route('catalog.new')"
        link-label="Ver novedades"
    />

    {{-- ===================== COLECCIONES ===================== --}}
    @if ($collections->isNotEmpty())
        <section class="bg-bone py-16 lg:py-24">
            <div class="container-editorial">
                <x-storefront.section-header
                    class="reveal"
                    eyebrow="Líneas"
                    title="Colecciones"
                    :href="route('collections.index')"
                />

                <div class="reveal mt-10 grid gap-6 lg:mt-14 lg:grid-cols-3 lg:gap-8">
                    @foreach ($collections as $collection)
                        @php $colImg = $resolveImg($collection->cover_image); @endphp
                        <a href="{{ route('collection.show', $collection->slug) }}"
                           class="group relative block aspect-[16/10] overflow-hidden bg-mist lg:aspect-[4/5]">
                            @if ($colImg)
                                <img src="{{ $colImg }}" alt="{{ $collection->name }}"
                                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1.2s] group-hover:scale-105"
                                     style="transition-timing-function: var(--ease-luxury);" loading="lazy">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-noir to-ink">
                                    <span class="font-display text-7xl font-semibold text-bone/10">RS</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-noir/75 via-noir/10 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-7 lg:p-9">
                                <h3 class="font-serif text-2xl font-medium text-bone lg:text-3xl">{{ $collection->name }}</h3>
                                @if ($collection->description)
                                    <p class="mt-2 max-w-sm text-sm font-light text-bone/70 line-clamp-2">{{ $collection->description }}</p>
                                @endif
                                <span class="mt-4 inline-flex items-center gap-2 font-sans text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-bone">
                                    Ver colección
                                    <svg class="h-3.5 w-3.5 transition-transform duration-500 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== MÁS VENDIDOS ===================== --}}
    <x-storefront.product-collection
        eyebrow="Favoritos"
        title="Los más vendidos"
        subtitle="Lo que las mujeres RodSant eligen para entrenar."
        :products="$bestSellers"
        :href="route('catalog.index')"
        :dark="true"
    />

    {{-- ===================== INSTAGRAM FEED ===================== --}}
    <section class="bg-bone py-16 lg:py-24">
        <div class="container-editorial">
            <x-storefront.section-header
                class="reveal"
                center
                :eyebrow="'@' . $instagramHandle"
                title="Síguenos en Instagram"
                subtitle="Entrenamientos, lanzamientos y comunidad."
                :href="$instagram ?? 'https://instagram.com/' . $instagramHandle"
                link-label="Seguir"
            />

            <div class="reveal mt-10 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:mt-14 lg:grid-cols-6 lg:gap-3">
                @for ($i = 0; $i < 6; $i++)
                    <a href="{{ $instagram ?? 'https://instagram.com/' . $instagramHandle }}" target="_blank" rel="noopener"
                       class="group relative block aspect-square overflow-hidden bg-noir">
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br {{ $i % 2 === 0 ? 'from-ink to-noir' : 'from-noir to-ink' }}">
                            <span class="font-display text-3xl font-semibold text-bone/10">RS</span>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center bg-noir/0 opacity-0 transition-all duration-500 group-hover:bg-noir/40 group-hover:opacity-100">
                            <svg class="h-6 w-6 text-bone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17.5" cy="6.5" r="0.6" fill="currentColor" />
                            </svg>
                        </div>
                    </a>
                @endfor
            </div>
        </div>
    </section>

    {{-- ===================== NEWSLETTER ===================== --}}
    <section class="relative overflow-hidden bg-noir text-bone">
        <span class="pointer-events-none absolute -right-12 top-1/2 -translate-y-1/2 select-none font-display text-[14rem] font-bold leading-none text-bone/[0.03]">RS</span>
        <div class="container-editorial relative py-20 lg:py-28">
            <div
                x-data="{ done: false, email: '' }"
                class="reveal mx-auto max-w-xl text-center"
            >
                <p class="eyebrow text-bone/50">Comunidad RodSant</p>
                <h2 class="mt-5 font-display text-3xl font-extrabold uppercase leading-[0.95] tracking-tight text-bone sm:text-4xl lg:text-5xl">
                    Acceso anticipado a cada lanzamiento
                </h2>
                <p class="mx-auto mt-5 max-w-md text-base font-light leading-relaxed text-bone/65">
                    Suscríbete y recibe nuevos lanzamientos, ofertas y consejos de entrenamiento antes que nadie.
                </p>

                <form
                    @submit.prevent="done = true"
                    x-show="!done"
                    class="mx-auto mt-10 flex max-w-md flex-col gap-3 sm:flex-row"
                >
                    <input
                        type="email"
                        x-model="email"
                        required
                        placeholder="tu@correo.com"
                        class="w-full border border-bone/25 bg-transparent px-5 py-4 font-sans text-sm text-bone placeholder:text-bone/40 focus:border-bone focus:outline-none focus:ring-0"
                    >
                    <button type="submit" class="btn-bone shrink-0 whitespace-nowrap">Suscribirme</button>
                </form>

                <p
                    x-show="done"
                    x-cloak
                    x-transition
                    class="mt-10 font-serif text-xl font-light text-bone/90"
                >
                    Gracias por unirte. Pronto sabrás de nosotros. ✶
                </p>

                <p class="mt-5 font-sans text-[0.65rem] uppercase tracking-[0.18em] text-bone/35">
                    Sin spam. Cancela cuando quieras.
                </p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
