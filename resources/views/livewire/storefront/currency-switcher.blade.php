<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="fixed bottom-5 left-5 z-40 print:hidden"
>
    {{-- Menú --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute bottom-full left-0 mb-2 w-52 border border-mist bg-white shadow-[0_18px_50px_-20px_rgba(10,10,10,0.45)]"
    >
        <p class="border-b border-mist px-4 py-2.5 font-sans text-[0.6rem] uppercase tracking-[0.2em] text-ash">Moneda</p>
        @foreach ($currencies as $code => $c)
            <button
                type="button"
                wire:click="select('{{ $code }}')"
                @click="open = false"
                class="flex w-full items-center justify-between px-4 py-2.5 text-left font-sans text-sm transition-colors hover:bg-bone {{ $current === $code ? 'bg-bone font-semibold text-noir' : 'text-noir/80' }}"
            >
                <span class="flex items-center gap-2.5">
                    <x-storefront.flag :code="$code" />
                    {{ $c['label'] }}
                </span>
                <span class="font-sans text-[0.7rem] tracking-wide text-ash">{{ $c['symbol'] }} {{ $code }}</span>
            </button>
        @endforeach
    </div>

    {{-- Botón flotante --}}
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-2 border border-noir/15 bg-white/95 px-4 py-2.5 shadow-[0_10px_30px_-12px_rgba(10,10,10,0.4)] backdrop-blur transition-colors hover:border-noir/40"
        aria-label="Cambiar moneda"
    >
        <x-storefront.flag :code="$current" />
        <span class="font-sans text-[0.72rem] font-semibold uppercase tracking-[0.12em] text-noir">{{ $current }}</span>
        <svg class="h-3 w-3 text-ash transition-transform duration-200" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>
</div>
