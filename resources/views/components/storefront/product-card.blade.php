@props([
    'product',
    'eager' => false,
])

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $resolveImage = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path);
    };

    $images = $product->relationLoaded('images') ? $product->images : collect();
    $primary = $product->primaryImage ?? $images->first();
    $secondary = $images->where('id', '!=', optional($primary)->id)->first();

    $primaryUrl = $resolveImage(optional($primary)->path);
    $secondaryUrl = $resolveImage(optional($secondary)->path);

    // Colores disponibles (swatches) a partir de las variantes.
    $colors = $product->variants
        ->flatMap(fn ($variant) => $variant->attributeValues)
        ->filter(fn ($value) => optional($value->attribute)->slug === 'color')
        ->unique('value')
        ->take(5);

    // Disponibilidad agregada (si todas las variantes tienen inventario cargado).
    $available = $product->variants->sum(fn ($variant) => $variant->availableStock());
    $isSoldOut = $product->variants->isNotEmpty() && $available <= 0;

    $url = route('product.show', $product->slug);
@endphp

<article class="group relative flex flex-col transition-transform duration-500 hover:-translate-y-1.5" style="transition-timing-function: var(--ease-luxury);">
    {{-- Imagen --}}
    <a href="{{ $url }}" class="relative block aspect-[3/4] overflow-hidden bg-mist transition-shadow duration-500 group-hover:shadow-[0_30px_60px_-30px_rgba(10,10,10,0.45)]" aria-label="{{ $product->name }}">
        @if ($primaryUrl)
            <img
                src="{{ $primaryUrl }}"
                alt="{{ optional($primary)->alt ?? $product->name }}"
                @if ($secondaryUrl) x-data="{}" @endif
                class="absolute inset-0 h-full w-full object-cover transition-all duration-700 {{ $secondaryUrl ? 'group-hover:opacity-0' : 'group-hover:scale-[1.04]' }}"
                style="transition-timing-function: var(--ease-luxury);"
                loading="{{ $eager ? 'eager' : 'lazy' }}"
                decoding="async"
            >
            @if ($secondaryUrl)
                <img
                    src="{{ $secondaryUrl }}"
                    alt="{{ $product->name }}"
                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100"
                    loading="lazy"
                    decoding="async"
                >
            @endif
        @else
            {{-- Fallback monograma RS --}}
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-mist to-bone">
                <span class="font-display text-5xl font-semibold tracking-tight text-noir/15 transition-transform duration-700 group-hover:scale-110">RS</span>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            @if ($product->is_new)
                <span class="bg-noir px-2.5 py-1 font-sans text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-bone">Nuevo</span>
            @endif
            @if ($product->isOnSale())
                <span class="bg-bone px-2.5 py-1 font-sans text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-noir">−{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        @if ($isSoldOut)
            <div class="absolute inset-x-0 bottom-0 bg-noir/85 py-2 text-center font-sans text-[0.6rem] font-semibold uppercase tracking-[0.22em] text-bone">
                Agotado
            </div>
        @endif

        {{-- Favorito (preparado) --}}
        <button
            type="button"
            @click.prevent
            class="absolute right-3 top-3 grid h-9 w-9 translate-y-1 place-items-center bg-bone/0 text-noir opacity-0 transition-all duration-500 hover:bg-bone group-hover:translate-y-0 group-hover:opacity-100"
            aria-label="Añadir a favoritos"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 20s-7-4.4-9.3-9C1.2 8 2.6 4.8 5.8 4.8c1.9 0 3.3 1.1 4.2 2.4.9-1.3 2.3-2.4 4.2-2.4 3.2 0 4.6 3.2 3.1 6.2C19 15.6 12 20 12 20Z" stroke-linejoin="round" />
            </svg>
        </button>
    </a>

    {{-- Información --}}
    <div class="flex flex-1 flex-col pt-4">
        @if ($product->category)
            <p class="font-sans text-[0.62rem] uppercase tracking-[0.2em] text-ash">{{ $product->category->name }}</p>
        @endif

        <h3 class="mt-1.5 font-serif text-[1.05rem] font-medium leading-snug text-noir">
            <a href="{{ $url }}" class="transition-colors hover:text-ink">{{ $product->name }}</a>
        </h3>

        <div class="mt-2 flex items-baseline gap-2">
            <span class="font-sans text-sm font-medium text-noir">{{ $product->price_formatted }}</span>
            @if ($product->isOnSale())
                <span class="font-sans text-xs text-ash line-through">{{ \App\Support\Money::format($product->base_price) }}</span>
            @endif
        </div>

        {{-- Swatches de color --}}
        @if ($colors->isNotEmpty())
            <div class="mt-3 flex items-center gap-1.5">
                @foreach ($colors as $color)
                    <span
                        class="h-3.5 w-3.5 rounded-full border border-noir/15"
                        style="background-color: {{ $color->hex() ?? '#e4e1dd' }};"
                        title="{{ $color->value }}"
                    ></span>
                @endforeach
            </div>
        @endif

        {{-- Añadir al carrito (selección de variante) --}}
        @unless ($isSoldOut)
            <livewire:storefront.add-to-cart :product="$product" :key="'atc-'.$product->id" />
        @endunless
    </div>
</article>
