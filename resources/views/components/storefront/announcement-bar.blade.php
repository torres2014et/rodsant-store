@php
    $symbol = config('rodsant.currency.symbol', '$');
    $freeFrom = (int) config('rodsant.shipping.free_from', 0);
    $freeShippingMsg = $freeFrom > 0
        ? 'Envío gratis en compras desde '.$symbol.number_format($freeFrom, 0, ',', '.')
        : 'Envío a todo el país';

    $messages = array_values(array_filter([
        $freeShippingMsg,
        'Tejidos técnicos · Pensados para entrenar',
        'Atención personalizada por WhatsApp',
    ]));
@endphp

<div
    x-data="{
        show: true,
        active: 0,
        total: {{ count($messages) }},
        init() {
            this.show = localStorage.getItem('rs_announcement_closed') !== '1';
            if (this.total > 1) {
                setInterval(() => { this.active = (this.active + 1) % this.total; }, 4500);
            }
        },
        close() {
            this.show = false;
            localStorage.setItem('rs_announcement_closed', '1');
        },
    }"
    x-show="show"
    x-cloak
    x-collapse
    class="relative bg-noir text-bone"
>
    <div class="container-editorial flex h-10 items-center justify-center">
        <div class="relative h-5 flex-1 overflow-hidden text-center">
            @foreach ($messages as $i => $message)
                <p
                    x-show="active === {{ $i }}"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 flex items-center justify-center font-sans text-[0.68rem] font-medium uppercase tracking-[0.22em] text-bone/90"
                >
                    {{ $message }}
                </p>
            @endforeach
        </div>

        <button
            type="button"
            @click="close()"
            class="absolute right-4 grid h-6 w-6 place-items-center text-bone/60 transition-colors hover:text-bone sm:right-8 lg:right-12"
            aria-label="Cerrar anuncio"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
            </svg>
        </button>
    </div>
</div>
