@php use App\Support\Money; @endphp

<div>
    <h2 class="flex items-center justify-between font-serif text-xl font-medium text-noir">
        Tu pedido
        <span class="font-sans text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ash">{{ $count }} {{ Str::plural('artículo', $count) }}</span>
    </h2>

    <ul class="mt-5 space-y-4 border-y border-mist py-5">
        @foreach ($items as $item)
            <li wire:key="sum-{{ $item['id'] }}" class="flex items-center gap-3">
                <div class="relative h-16 w-14 shrink-0 overflow-hidden bg-mist">
                    @if ($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-mist to-bone">
                            <span class="font-display text-base font-semibold text-noir/15">RS</span>
                        </div>
                    @endif
                    <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-noir px-1 font-sans text-[0.6rem] font-semibold text-bone">{{ $item['quantity'] }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-serif text-sm font-medium text-noir">{{ $item['name'] }}</p>
                    @if ($item['label'])
                        <p class="mt-0.5 font-sans text-[0.66rem] uppercase tracking-[0.12em] text-ash">{{ $item['label'] }}</p>
                    @endif
                </div>
                <span class="shrink-0 font-sans text-sm font-medium text-noir">{{ Money::format($item['line_total']) }}</span>
            </li>
        @endforeach
    </ul>

    <dl class="space-y-3 py-5 font-sans text-sm">
        <div class="flex items-center justify-between">
            <dt class="text-ash">Subtotal</dt>
            <dd class="font-medium text-noir">{{ Money::format($subtotal) }}</dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-ash">Envío</dt>
            <dd class="text-noir/70">Se coordina por WhatsApp</dd>
        </div>
    </dl>

    <div class="flex items-center justify-between border-t border-mist pt-5">
        <span class="font-sans text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-noir">Total</span>
        <span class="font-serif text-2xl font-medium text-noir">{{ Money::format($subtotal) }}</span>
    </div>
</div>
