@props([
    'light' => false,
    'monogramOnly' => false,
])

@php
    $wordmarkColor = $light ? 'text-bone' : 'text-noir';
    $ring = $light ? 'ring-1 ring-bone/15' : 'ring-1 ring-noir/10';
@endphp

<a href="{{ route('home') }}"
   {{ $attributes->merge(['class' => 'group inline-flex items-center gap-3', 'aria-label' => config('rodsant.brand.name')]) }}>
    {{-- Logo real (monograma RS). El JPG trae degradado de fondo: lo recortamos
         en círculo y lo escalamos para mostrar solo el círculo negro con el RS. --}}
    <span class="block h-9 w-9 shrink-0 overflow-hidden rounded-full bg-noir transition-transform duration-500 group-hover:scale-105 {{ $ring }}"
          style="transition-timing-function: var(--ease-luxury);">
        <img
            src="{{ asset('img/rodsant-logo.jpg') }}"
            alt="{{ config('rodsant.brand.name') }}"
            class="h-full w-full scale-[1.45] object-cover"
            loading="eager"
            decoding="async"
            width="36"
            height="36"
        >
    </span>

    @unless ($monogramOnly)
        <span class="font-display text-lg font-semibold uppercase tracking-[0.35em] leading-none {{ $wordmarkColor }}">
            RodSant
        </span>
    @endunless
</a>
