<div class="bg-bone">
    {{-- Cabecera de la página --}}
    <header class="border-b border-noir/10 bg-bone">
        <div class="container-editorial py-12 lg:py-16">
            @if ($contextEyebrow)
                <p class="eyebrow text-ash">{{ $contextEyebrow }}</p>
            @endif
            <h1 class="mt-3 font-display text-4xl font-extrabold uppercase leading-[0.95] tracking-tight text-noir sm:text-5xl lg:text-6xl">
                {{ $contextTitle }}
            </h1>
            @if ($contextDescription)
                <p class="mt-4 max-w-xl text-base font-light leading-relaxed text-ink/70">{{ $contextDescription }}</p>
            @endif
        </div>
    </header>

    <div class="container-editorial py-10 lg:py-14">
        <div class="lg:grid lg:grid-cols-[260px_1fr] lg:gap-12">

            {{-- ===================== FILTROS ===================== --}}
            <aside x-data="{ open: false }" class="mb-8 lg:mb-0">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center justify-between border border-noir/15 px-4 py-3 font-sans text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-noir lg:hidden"
                >
                    Filtrar y ordenar
                    <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div x-show="open" x-collapse class="mt-4 lg:mt-0 lg:!block" x-cloak>
                    {{-- Buscar --}}
                    <div class="mb-8">
                        <label class="mb-2 block font-sans text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-noir">Buscar</label>
                        <input
                            type="search"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Buscar prendas…"
                            class="w-full border border-noir/15 bg-transparent px-3 py-2.5 font-sans text-sm text-noir placeholder:text-ash focus:border-noir focus:outline-none focus:ring-0"
                        >
                    </div>

                    {{-- Categorías --}}
                    <div class="mb-8">
                        <p class="mb-3 font-sans text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-noir">Categorías</p>
                        <ul class="space-y-1.5">
                            <li>
                                <a href="{{ route('catalog.index') }}" wire:navigate
                                   class="font-sans text-sm transition-colors {{ ! $categoryId && ! $collectionId && ! $onlyNew && ! $onlySale ? 'font-semibold text-noir' : 'text-ink/70 hover:text-noir' }}">
                                    Toda la tienda
                                </a>
                            </li>
                            @foreach ($categories as $cat)
                                <li class="flex items-center justify-between">
                                    <a href="{{ route('category.show', $cat->slug) }}" wire:navigate
                                       class="font-sans text-sm transition-colors {{ $categoryId === $cat->id ? 'font-semibold text-noir' : 'text-ink/70 hover:text-noir' }}">
                                        {{ $cat->name }}
                                    </a>
                                    <span class="font-sans text-[0.7rem] text-ash">{{ $cat->products_count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Color --}}
                    @if (! empty($colorOptions))
                        <div class="mb-8">
                            <p class="mb-3 font-sans text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-noir">Color</p>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach ($colorOptions as $c)
                                    <button
                                        type="button"
                                        wire:click="toggleColor('{{ $c['value'] }}')"
                                        title="{{ $c['value'] }}"
                                        aria-label="{{ $c['value'] }}"
                                        class="relative h-7 w-7 rounded-full border transition-transform duration-200 hover:scale-110 {{ in_array($c['value'], $colors, true) ? 'border-noir ring-1 ring-noir ring-offset-2 ring-offset-bone' : 'border-noir/20' }}"
                                        style="background-color: {{ $c['hex'] ?? '#e4e1dd' }};"
                                    ></button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Talla --}}
                    @if (! empty($sizeOptions))
                        <div class="mb-8">
                            <p class="mb-3 font-sans text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-noir">Talla</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($sizeOptions as $s)
                                    <button
                                        type="button"
                                        wire:click="toggleSize('{{ $s['value'] }}')"
                                        class="min-w-10 border px-3 py-1.5 font-sans text-[0.78rem] font-medium transition-colors {{ in_array($s['value'], $sizes, true) ? 'border-noir bg-noir text-bone' : 'border-noir/20 text-noir hover:border-noir' }}"
                                    >{{ $s['value'] }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($hasFilters)
                        <button type="button" wire:click="clearFilters"
                                class="font-sans text-[0.72rem] font-medium uppercase tracking-[0.16em] text-ink underline underline-offset-4 transition-colors hover:text-noir">
                            Limpiar filtros
                        </button>
                    @endif
                </div>
            </aside>

            {{-- ===================== RESULTADOS ===================== --}}
            <div>
                {{-- Barra superior: conteo + orden --}}
                <div class="mb-8 flex flex-col gap-4 border-b border-noir/10 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-sans text-[0.78rem] uppercase tracking-[0.14em] text-ash">
                        {{ $products->total() }} {{ \Illuminate\Support\Str::plural('prenda', $products->total()) }}
                    </p>

                    <div class="flex items-center gap-2">
                        <label for="sort" class="font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Ordenar</label>
                        <select id="sort" wire:model.live="sort"
                                class="border border-noir/15 bg-transparent py-2 pl-3 pr-8 font-sans text-sm text-noir focus:border-noir focus:outline-none focus:ring-0">
                            <option value="relevancia">Relevancia</option>
                            <option value="nuevos">Más nuevos</option>
                            <option value="precio_asc">Precio: menor a mayor</option>
                            <option value="precio_desc">Precio: mayor a menor</option>
                            <option value="nombre">Nombre (A–Z)</option>
                        </select>
                    </div>
                </div>

                {{-- Chips de filtros activos --}}
                @if ($hasFilters)
                    <div class="mb-6 flex flex-wrap items-center gap-2">
                        @foreach ($colors as $c)
                            <button type="button" wire:click="toggleColor('{{ $c }}')"
                                    class="inline-flex items-center gap-1.5 bg-mist px-3 py-1.5 font-sans text-[0.7rem] uppercase tracking-[0.12em] text-noir hover:bg-noir hover:text-bone">
                                {{ $c }} <span aria-hidden="true">×</span>
                            </button>
                        @endforeach
                        @foreach ($sizes as $s)
                            <button type="button" wire:click="toggleSize('{{ $s }}')"
                                    class="inline-flex items-center gap-1.5 bg-mist px-3 py-1.5 font-sans text-[0.7rem] uppercase tracking-[0.12em] text-noir hover:bg-noir hover:text-bone">
                                Talla {{ $s }} <span aria-hidden="true">×</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Grilla de productos --}}
                @if ($products->isNotEmpty())
                    <div wire:loading.class="opacity-40" class="grid grid-cols-2 gap-x-4 gap-y-10 transition-opacity duration-200 sm:gap-x-6 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-14">
                        @foreach ($products as $product)
                            <x-storefront.product-card :product="$product" wire:key="prod-{{ $product->id }}" />
                        @endforeach
                    </div>

                    <div class="mt-14">
                        {{ $products->onEachSide(1)->links() }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <span class="font-display text-7xl font-bold text-noir/10">RS</span>
                        <p class="mt-6 font-serif text-xl text-noir">No encontramos prendas con esos filtros.</p>
                        <p class="mt-2 max-w-sm font-sans text-sm text-ink/60">Prueba quitando algún filtro o explora toda la tienda.</p>
                        @if ($hasFilters)
                            <button type="button" wire:click="clearFilters"
                                    class="mt-6 bg-noir px-7 py-3 font-sans text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-bone transition-colors hover:bg-ink">
                                Limpiar filtros
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
