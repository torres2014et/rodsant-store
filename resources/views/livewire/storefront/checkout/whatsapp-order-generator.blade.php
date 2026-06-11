@php use App\Support\Money; @endphp

<div
    x-data="{
        url: @js($whatsappUrl),
        storageKey: 'rs_wa_{{ $orderNumber }}',
        sent: @js($sent),
        send() {
            window.open(this.url, '_blank');
            this.sent = true;
            $wire.markSent();
        },
        init() {
            // Apertura automática (una sola vez por pedido); puede bloquearla el navegador.
            if (! localStorage.getItem(this.storageKey)) {
                localStorage.setItem(this.storageKey, '1');
                setTimeout(() => this.send(), 700);
            }
        }
    }"
    class="container-editorial py-12 lg:py-20"
>
    <div class="mx-auto max-w-2xl">
        {{-- Confirmación visual --}}
        <div class="text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-noir">
                <svg class="h-8 w-8 text-bone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="m5 12 5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <p class="eyebrow mt-6">Pedido recibido</p>
            <h1 class="mt-3 font-serif text-3xl font-medium text-noir sm:text-4xl">¡Gracias, {{ \Illuminate\Support\Str::of($customerName)->trim()->explode(' ')->first() }}!</h1>
            <p class="mx-auto mt-3 max-w-md font-sans text-sm font-light leading-relaxed text-ash">
                Tu pedido quedó registrado. Para confirmarlo y coordinar el pago y la entrega,
                envíanoslo por WhatsApp con un solo toque.
            </p>
        </div>

        {{-- Número de pedido --}}
        <div class="mt-8 flex flex-col items-center justify-between gap-3 border border-mist bg-bone px-6 py-5 sm:flex-row">
            <div>
                <p class="font-sans text-[0.65rem] uppercase tracking-[0.2em] text-ash">Número de pedido</p>
                <p class="mt-1 font-display text-xl font-semibold tracking-wide text-noir">{{ $orderNumber }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-noir/5 px-3 py-1.5 font-sans text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-noir/70">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                Pendiente
            </span>
        </div>

        {{-- Resumen --}}
        <div class="mt-6 border border-mist">
            <div class="border-b border-mist px-6 py-4">
                <h2 class="font-serif text-lg font-medium text-noir">Resumen</h2>
            </div>
            <ul class="divide-y divide-mist px-6">
                @foreach ($items as $item)
                    <li class="flex items-start justify-between gap-4 py-4">
                        <div>
                            <p class="font-serif text-base font-medium text-noir">{{ $item['name'] }}</p>
                            @if ($item['label'])
                                <p class="mt-0.5 font-sans text-[0.68rem] uppercase tracking-[0.12em] text-ash">{{ $item['label'] }}</p>
                            @endif
                            <p class="mt-1 font-sans text-xs text-ash">Cantidad: {{ $item['quantity'] }}</p>
                        </div>
                        <span class="shrink-0 font-sans text-sm font-medium text-noir">{{ Money::format($item['line_total']) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="space-y-2 border-t border-mist px-6 py-4 font-sans text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-ash">Subtotal</span>
                    <span class="text-noir">{{ Money::format($subtotal) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ash">Envío</span>
                    <span class="text-noir/70">Se coordina por WhatsApp</span>
                </div>
                <div class="flex items-center justify-between border-t border-mist pt-3">
                    <span class="font-semibold uppercase tracking-[0.16em] text-noir">Total</span>
                    <span class="font-serif text-xl font-medium text-noir">{{ Money::format($total) }}</span>
                </div>
            </div>
        </div>

        {{-- Dirección --}}
        @if ($addressLine)
            <div class="mt-6 border border-mist px-6 py-5">
                <p class="font-sans text-[0.65rem] uppercase tracking-[0.2em] text-ash">Entrega</p>
                <p class="mt-2 font-sans text-sm text-noir">{{ $addressLine }}</p>
                <p class="font-sans text-sm text-noir">{{ $addressCity }}</p>
                @if ($addressReferences)
                    <p class="mt-1 font-sans text-xs text-ash">Ref: {{ $addressReferences }}</p>
                @endif
            </div>
        @endif

        {{-- CTA WhatsApp --}}
        <div class="mt-8">
            <a :href="url" target="_blank" rel="noopener" @click="send()"
               class="flex w-full items-center justify-center gap-3 bg-[#25D366] py-4 font-sans text-[0.78rem] font-semibold uppercase tracking-[0.16em] text-white transition-opacity duration-300 hover:opacity-90">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2Zm5.2 13.9c-.2.6-1.2 1.2-1.7 1.2-.4 0-1 .1-3.4-.9-2.9-1.2-4.7-4.2-4.8-4.4-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 .9-2.2.2-.2.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .6l-.4.6c-.1.2-.3.3-.1.6.5.8 1 1.4 1.7 1.9.6.4 1 .6 1.3.7.2.1.4.1.5-.1l.7-.8c.2-.2.3-.2.6-.1l1.9.9c.3.1.5.2.5.4.1.2.1.9-.1 1.5Z" />
                </svg>
                Enviar pedido por WhatsApp
            </a>

            <p x-show="sent" x-cloak class="mt-3 flex items-center justify-center gap-2 font-sans text-[0.72rem] text-green-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m5 12 5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Abrimos WhatsApp. Si no se abrió, toca el botón de arriba.
            </p>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" wire:navigate
                   class="font-sans text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-ash transition-colors hover:text-noir">
                    Seguir explorando
                </a>
            </div>
        </div>
    </div>
</div>
