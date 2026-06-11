@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $resolveImg = static fn (?string $path): ?string => $path
        ? (Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path))
        : null;

    $ogImage = $resolveImg($product->primaryImage?->path ?? $product->images->first()?->path);

    // Datos estructurados Schema.org Product/Offer.
    $available = $product->variants->sum(fn ($v) => $v->availableStock());
    $structured = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $product->short_description ?? $product->meta_description,
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => config('rodsant.brand.name', 'RodSant Store')],
        'category' => $product->category?->name,
        'image' => array_values(array_filter($product->images->map(fn ($i) => $resolveImg($i->path))->all())),
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => config('rodsant.currency.code', 'COP'),
            'price' => (string) (int) (float) $product->effectivePrice(),
            'availability' => $available > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('product.show', $product->slug),
        ],
    ];
@endphp

<x-layouts.storefront
    :title="$product->meta_title ?? $product->name"
    :description="$product->short_description ?? $product->meta_description"
    type="product"
    :image="$ogImage"
>
    @push('structured-data')
        <script type="application/ld+json">
            {!! json_encode($structured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endpush

    {{-- Breadcrumb --}}
    <nav aria-label="Ruta de navegación" class="border-b border-noir/10 bg-bone">
        <div class="container-editorial flex flex-wrap items-center gap-2 py-4 font-sans text-[0.72rem] uppercase tracking-[0.14em] text-ash">
            <a href="{{ route('home') }}" wire:navigate class="transition-colors hover:text-noir">Inicio</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('catalog.index') }}" wire:navigate class="transition-colors hover:text-noir">Tienda</a>
            @if ($product->category)
                <span aria-hidden="true">/</span>
                <a href="{{ route('category.show', $product->category->slug) }}" wire:navigate class="transition-colors hover:text-noir">{{ $product->category->name }}</a>
            @endif
            <span aria-hidden="true">/</span>
            <span class="text-noir">{{ $product->name }}</span>
        </div>
    </nav>

    {{-- Galería + compra (interactivo) --}}
    <livewire:storefront.product-detail :product="$product" />

    {{-- Descripción --}}
    @if ($product->description)
        <section class="border-t border-noir/10 bg-bone">
            <div class="container-editorial grid gap-8 py-14 lg:grid-cols-[280px_1fr] lg:gap-16 lg:py-20">
                <div>
                    <p class="eyebrow text-ash">Detalles</p>
                    <h2 class="mt-3 font-display text-2xl font-extrabold uppercase tracking-tight text-noir">Sobre la prenda</h2>
                </div>
                <div class="max-w-2xl space-y-4 font-sans text-[0.95rem] font-light leading-relaxed text-ink/80">
                    @foreach (preg_split('/\n+/', (string) $product->description) as $paragraph)
                        @if (trim($paragraph) !== '')
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Relacionados --}}
    <x-storefront.product-collection
        eyebrow="También te puede gustar"
        title="Completa tu look"
        :products="$related"
        :href="route('category.show', $product->category?->slug ?? '')"
        link-label="Ver categoría"
    />
</x-layouts.storefront>
