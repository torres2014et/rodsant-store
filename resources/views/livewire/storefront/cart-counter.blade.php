<a href="{{ route('cart.index') }}"
   class="relative grid h-10 w-10 place-items-center text-noir transition-opacity hover:opacity-60"
   aria-label="Carrito ({{ $count }})">
    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M6 7h12l-1 13H7L6 7Z" stroke-linejoin="round" />
        <path d="M9 7a3 3 0 0 1 6 0" stroke-linecap="round" />
    </svg>
    @if ($count > 0)
        <span
            class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-noir px-1 font-sans text-[0.6rem] font-semibold leading-none text-bone"
            x-data
            x-init="$el.animate([{ transform: 'scale(0.6)' }, { transform: 'scale(1)' }], { duration: 250, easing: 'cubic-bezier(0.16,1,0.3,1)' })"
        >{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
