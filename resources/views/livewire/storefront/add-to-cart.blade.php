<div x-data="{ open: false }" class="mt-3">
    {{-- Disparador --}}
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-center gap-2 border border-noir/15 bg-bone py-2.5 font-sans text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-noir transition-colors duration-300 hover:bg-noir hover:text-bone"
    >
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M6 7h12l-1 13H7L6 7Z" stroke-linejoin="round" />
            <path d="M9 7a3 3 0 0 1 6 0" stroke-linecap="round" />
        </svg>
        <span x-show="!open">Agregar</span>
        <span x-show="open" x-cloak>Cerrar</span>
    </button>

    {{-- Panel de selección --}}
    <div x-show="open" x-cloak x-collapse class="pt-3">
        @if (! empty($sizes))
            <div class="mb-3">
                <p class="mb-1.5 font-sans text-[0.6rem] uppercase tracking-[0.18em] text-ash">Talla</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($sizes as $s)
                        <button
                            type="button"
                            wire:click="selectSize('{{ $s['value'] }}')"
                            class="min-w-9 border px-2.5 py-1.5 font-sans text-[0.72rem] font-medium transition-colors duration-200 {{ $size === $s['value'] ? 'border-noir bg-noir text-bone' : 'border-noir/20 text-noir hover:border-noir' }}"
                        >{{ $s['value'] }}</button>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($colors))
            <div class="mb-3">
                <p class="mb-1.5 font-sans text-[0.6rem] uppercase tracking-[0.18em] text-ash">
                    Color @if ($color)<span class="text-noir/70">· {{ $color }}</span>@endif
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($colors as $c)
                        <button
                            type="button"
                            wire:click="selectColor('{{ $c['value'] }}')"
                            title="{{ $c['value'] }}"
                            aria-label="{{ $c['value'] }}"
                            class="relative h-6 w-6 rounded-full border transition-transform duration-200 {{ $color === $c['value'] ? 'border-noir ring-1 ring-noir ring-offset-2 ring-offset-bone' : 'border-noir/20 hover:scale-110' }}"
                            style="background-color: {{ $c['hex'] ?? '#e4e1dd' }};"
                        ></button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Cantidad --}}
        <div class="mb-3 flex items-center gap-3">
            <p class="font-sans text-[0.6rem] uppercase tracking-[0.18em] text-ash">Cantidad</p>
            <div class="flex items-center border border-noir/15">
                <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                        class="grid h-8 w-8 place-items-center text-noir transition-colors hover:bg-mist" aria-label="Restar">−</button>
                <span class="grid h-8 w-9 place-items-center font-sans text-sm text-noir">{{ $quantity }}</span>
                <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})"
                        class="grid h-8 w-8 place-items-center text-noir transition-colors hover:bg-mist" aria-label="Sumar">+</button>
            </div>
        </div>

        @error('variant')
            <p class="mb-2 font-sans text-[0.72rem] text-red-700">{{ $message }}</p>
        @enderror

        <button
            type="button"
            wire:click="addToCart"
            wire:loading.attr="disabled"
            wire:target="addToCart"
            class="flex w-full items-center justify-center gap-2 bg-noir py-3 font-sans text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-bone transition-colors duration-300 hover:bg-ink disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="addToCart">Añadir al carrito</span>
            <span wire:loading wire:target="addToCart">Añadiendo…</span>
        </button>

        @if ($added)
            <p class="mt-2 flex items-center justify-center gap-1.5 font-sans text-[0.7rem] text-green-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m5 12 5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Añadido ·
                <a href="{{ route('cart.index') }}" class="underline underline-offset-2 hover:text-noir">Ver carrito</a>
            </p>
        @endif
    </div>
</div>
