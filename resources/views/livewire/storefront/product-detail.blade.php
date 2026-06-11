<div class="container-editorial py-8 lg:py-14">
    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

        {{-- ===================== GALERÍA ===================== --}}
        <div x-data="{ active: 0 }" class="lg:sticky lg:top-28 lg:self-start">
            <div class="relative aspect-[3/4] overflow-hidden bg-mist">
                @forelse ($images as $i => $image)
                    <img
                        src="{{ $image['url'] }}"
                        alt="{{ $image['alt'] }}"
                        x-show="active === {{ $i }}"
                        x-transition.opacity.duration.400ms
                        class="absolute inset-0 h-full w-full object-cover"
                        @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                    >
                @empty
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="font-display text-7xl font-semibold text-noir/10">RS</span>
                    </div>
                @endforelse
            </div>

            @if (count($images) > 1)
                <div class="mt-4 flex gap-3">
                    @foreach ($images as $i => $image)
                        <button
                            type="button"
                            @click="active = {{ $i }}"
                            class="relative aspect-[3/4] w-20 overflow-hidden bg-mist transition-opacity"
                            :class="active === {{ $i }} ? 'ring-1 ring-noir ring-offset-2 ring-offset-bone' : 'opacity-60 hover:opacity-100'"
                            aria-label="Ver imagen {{ $i + 1 }}"
                        >
                            <img src="{{ $image['url'] }}" alt="" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===================== INFORMACIÓN / COMPRA ===================== --}}
        <div>
            @if ($categoryName)
                <a href="{{ route('category.show', $categorySlug) }}" wire:navigate
                   class="eyebrow text-ash transition-colors hover:text-noir">{{ $categoryName }}</a>
            @endif

            <h1 class="mt-3 font-display text-3xl font-extrabold uppercase leading-[0.98] tracking-tight text-noir sm:text-4xl lg:text-5xl">
                {{ $productName }}
            </h1>

            <div class="mt-5 flex items-center gap-3">
                <span class="font-sans text-2xl font-medium text-noir">{{ $priceFormatted }}</span>
                @if ($isOnSale)
                    <span class="font-sans text-base text-ash line-through">{{ $basePriceFormatted }}</span>
                    <span class="bg-noir px-2 py-1 font-sans text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-bone">−{{ $discountPct }}%</span>
                @endif
            </div>

            <div class="mt-8 h-px w-full bg-noir/10"></div>

            {{-- Color --}}
            @if (! empty($colors))
                <div class="mt-8">
                    <p class="mb-3 font-sans text-[0.66rem] font-semibold uppercase tracking-[0.2em] text-noir">
                        Color @if ($color)<span class="ml-1 font-normal text-ink/60">· {{ $color }}</span>@endif
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        @foreach ($colors as $c)
                            <button
                                type="button"
                                wire:click="selectColor('{{ $c['value'] }}')"
                                title="{{ $c['value'] }}"
                                aria-label="{{ $c['value'] }}"
                                class="relative h-8 w-8 rounded-full border transition-transform duration-200 hover:scale-110 {{ $color === $c['value'] ? 'border-noir ring-1 ring-noir ring-offset-2 ring-offset-bone' : 'border-noir/20' }}"
                                style="background-color: {{ $c['hex'] ?? '#e4e1dd' }};"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Talla --}}
            @if (! empty($sizes))
                <div class="mt-8">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="font-sans text-[0.66rem] font-semibold uppercase tracking-[0.2em] text-noir">
                            Talla @if ($size)<span class="ml-1 font-normal text-ink/60">· {{ $size }}</span>@endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($sizes as $s)
                            <button
                                type="button"
                                wire:click="selectSize('{{ $s['value'] }}')"
                                class="min-w-12 border px-4 py-2.5 font-sans text-sm font-medium transition-colors {{ $size === $s['value'] ? 'border-noir bg-noir text-bone' : 'border-noir/20 text-noir hover:border-noir' }}"
                            >{{ $s['value'] }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Disponibilidad --}}
            <div class="mt-6">
                @if ($available === null)
                    <p class="font-sans text-[0.78rem] text-ink/60">Elige tus opciones para ver disponibilidad.</p>
                @elseif ($available <= 0)
                    <p class="inline-flex items-center gap-2 font-sans text-[0.78rem] font-medium text-red-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span> Agotado en esta combinación
                    </p>
                @elseif ($available <= 5)
                    <p class="inline-flex items-center gap-2 font-sans text-[0.78rem] font-medium text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> ¡Últimas {{ $available }} unidades!
                    </p>
                @else
                    <p class="inline-flex items-center gap-2 font-sans text-[0.78rem] font-medium text-green-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> Disponible
                    </p>
                @endif
            </div>

            {{-- Cantidad + Añadir --}}
            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-stretch">
                <div class="flex items-center border border-noir/15">
                    <button type="button" wire:click="decrement"
                            class="grid h-12 w-12 place-items-center text-lg text-noir transition-colors hover:bg-mist disabled:opacity-40"
                            @disabled(($available ?? 0) <= 0) aria-label="Restar">−</button>
                    <span class="grid h-12 w-12 place-items-center font-sans text-base text-noir">{{ $quantity }}</span>
                    <button type="button" wire:click="increment"
                            class="grid h-12 w-12 place-items-center text-lg text-noir transition-colors hover:bg-mist disabled:opacity-40"
                            @disabled(($available ?? 0) <= 0) aria-label="Sumar">+</button>
                </div>

                <button
                    type="button"
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    wire:target="addToCart"
                    @disabled($available !== null && $available <= 0)
                    class="flex flex-1 items-center justify-center gap-2 bg-noir px-8 py-4 font-sans text-[0.72rem] font-semibold uppercase tracking-[0.2em] text-bone transition-colors duration-300 hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="addToCart">
                        @if ($available !== null && $available <= 0) Agotado @else Añadir al carrito @endif
                    </span>
                    <span wire:loading wire:target="addToCart">Añadiendo…</span>
                </button>
            </div>

            @error('variant')
                <p class="mt-3 font-sans text-[0.78rem] text-red-700">{{ $message }}</p>
            @enderror

            @if ($added)
                <div class="mt-4 flex items-center justify-between border border-green-700/30 bg-green-50 px-4 py-3">
                    <p class="flex items-center gap-2 font-sans text-[0.8rem] text-green-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m5 12 5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Añadido a tu carrito
                    </p>
                    <a href="{{ route('cart.index') }}" wire:navigate
                       class="font-sans text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-green-900 underline underline-offset-4">
                        Ver carrito
                    </a>
                </div>
            @endif

            {{-- Garantías / info de compra --}}
            <ul class="mt-10 space-y-3 border-t border-noir/10 pt-8">
                <li class="flex items-center gap-3 font-sans text-[0.82rem] text-ink/75">
                    <svg class="h-4 w-4 text-noir" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7h13v8H3zM16 10h3l2 3v2h-5" stroke-linejoin="round"/><circle cx="7" cy="17" r="1.5"/><circle cx="18" cy="17" r="1.5"/></svg>
                    Envío a todo el país
                </li>
                <li class="flex items-center gap-3 font-sans text-[0.82rem] text-ink/75">
                    <svg class="h-4 w-4 text-noir" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.5 8.5 0 1 1-4-7.2" stroke-linecap="round"/><path d="M21 4v4h-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Cambios y devoluciones fáciles
                </li>
                <li class="flex items-center gap-3 font-sans text-[0.82rem] text-ink/75">
                    <svg class="h-4 w-4 text-noir" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21s-7-4.5-7-10a7 7 0 0 1 14 0c0 5.5-7 10-7 10Z" stroke-linejoin="round"/></svg>
                    Atención personalizada por WhatsApp
                </li>
            </ul>
        </div>
    </div>
</div>
