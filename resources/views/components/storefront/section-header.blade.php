@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'href' => null,
    'linkLabel' => 'Ver todo',
    'center' => false,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4 ' . ($center ? 'flex-col items-center text-center' : '')]) }}>
    <div class="{{ $center ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">
        @if ($eyebrow)
            <p class="eyebrow mb-3">{{ $eyebrow }}</p>
        @endif
        <h2 class="font-serif text-3xl font-medium leading-tight text-noir sm:text-4xl lg:text-[2.75rem]">
            {{ $title }}
        </h2>
        @if ($subtitle)
            <p class="mt-3 max-w-xl text-base font-light leading-relaxed text-ash {{ $center ? 'mx-auto' : '' }}">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if ($href)
        <a href="{{ $href }}"
           class="group inline-flex items-center gap-2 font-sans text-[0.72rem] font-semibold uppercase tracking-[0.2em] text-noir transition-opacity hover:opacity-70 {{ $center ? 'mx-auto mt-2' : 'shrink-0 pb-1.5' }}">
            {{ $linkLabel }}
            <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
    @endif
</div>
