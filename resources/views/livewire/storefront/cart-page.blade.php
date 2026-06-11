@php use App\Support\Money; @endphp

<div class="container-editorial py-12 lg:py-16">
    <div class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="eyebrow mb-2">Tu selección</p>
            <h1 class="font-serif text-3xl font-medium text-noir sm:text-4xl">Carrito</h1>
        </div>
        <a href="{{ route('catalog.index') }}" class="hidden font-sans text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-ash transition-colors hover:text-noir sm:inline">
            Seguir comprando
        </a>
    </div>

    @if (count($items) === 0)
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center justify-center border border-mist py-24 text-center">
            <div class="grid h-16 w-16 place-items-center rounded-full bg-bone">
                <svg class="h-7 w-7 text-ash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                    <path d="M6 7h12l-1 13H7L6 7Z" stroke-linejoin="round" />
                    <path d="M9 7a3 3 0 0 1 6 0" stroke-linecap="round" />
                </svg>
            </div>
            <h2 class="mt-6 font-serif text-2xl font-medium text-noir">Tu carrito está vacío</h2>
            <p class="mt-2 max-w-sm text-sm font-light text-ash">Aún no has añadido prendas. Explora la tienda y encuentra tu próximo esencial.</p>
            <a href="{{ route('catalog.index') }}" class="btn-noir mt-8">Explorar la tienda</a>
        </div>
    @else
        @if ($notice)
            <div class="mb-6 border border-amber-300 bg-amber-50 px-4 py-3 font-sans text-sm text-amber-800">
                {{ $notice }}
            </div>
        @endif

        <div class="grid gap-10 lg:grid-cols-[1fr_360px] lg:gap-16">
            {{-- Líneas --}}
            <div class="divide-y divide-mist border-y border-mist">
                @foreach ($items as $item)
                    <div wire:key="cart-{{ $item['id'] }}" class="flex gap-4 py-6 sm:gap-6">
                        <a href="{{ $item['slug'] ? route('product.show', $item['slug']) : '#' }}"
                           class="relative block h-28 w-24 shrink-0 overflow-hidden bg-mist sm:h-32 sm:w-28">
                            @if ($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-mist to-bone">
                                    <span class="font-display text-2xl font-semibold text-noir/15">RS</span>
                                </div>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-serif text-lg font-medium leading-snug text-noir">
                                        <a href="{{ $item['slug'] ? route('product.show', $item['slug']) : '#' }}" class="hover:text-ink">{{ $item['name'] }}</a>
                                    </h3>
                                    @if ($item['label'])
                                        <p class="mt-1 font-sans text-[0.72rem] uppercase tracking-[0.14em] text-ash">{{ $item['label'] }}</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="remove({{ $item['id'] }})"
                                        class="shrink-0 text-ash transition-colors hover:text-noir" aria-label="Quitar">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-auto flex items-end justify-between gap-4 pt-4">
                                {{-- Stepper --}}
                                <div class="flex items-center border border-noir/15">
                                    <button type="button" wire:click="decrement({{ $item['id'] }})"
                                            class="grid h-9 w-9 place-items-center text-noir transition-colors hover:bg-mist" aria-label="Restar">−</button>
                                    <span class="grid h-9 w-10 place-items-center font-sans text-sm text-noir" wire:loading.class="opacity-40" wire:target="increment,decrement">{{ $item['quantity'] }}</span>
                                    <button type="button" wire:click="increment({{ $item['id'] }})"
                                            class="grid h-9 w-9 place-items-center text-noir transition-colors hover:bg-mist disabled:opacity-40" aria-label="Sumar">+</button>
                                </div>

                                <div class="text-right">
                                    <p class="font-sans text-sm font-medium text-noir">{{ Money::format($item['line_total']) }}</p>
                                    @if ($item['quantity'] > 1)
                                        <p class="mt-0.5 font-sans text-[0.7rem] text-ash">{{ Money::format($item['unit_price']) }} c/u</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Resumen --}}
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="border border-mist bg-white p-6 lg:p-7">
                    <h2 class="font-serif text-xl font-medium text-noir">Resumen</h2>

                    <dl class="mt-5 space-y-3 border-b border-mist pb-5 font-sans text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-ash">Subtotal</dt>
                            <dd class="font-medium text-noir">{{ Money::format($subtotal) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ash">Envío</dt>
                            <dd class="text-noir/70">Se coordina por WhatsApp</dd>
                        </div>
                    </dl>

                    <div class="flex items-center justify-between py-5">
                        <span class="font-sans text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-noir">Total</span>
                        <span class="font-serif text-2xl font-medium text-noir">{{ Money::format($subtotal) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn-noir w-full">Finalizar compra</a>

                    <p class="mt-4 flex items-center justify-center gap-2 text-center font-sans text-[0.7rem] text-ash">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="5" y="11" width="14" height="9" rx="1.5" /><path d="M8 11V8a4 4 0 0 1 8 0v3" />
                        </svg>
                        Cierre seguro · Coordinamos por WhatsApp
                    </p>
                </div>
            </aside>
        </div>
    @endif
</div>
