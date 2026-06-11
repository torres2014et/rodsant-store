<x-layouts.storefront :title="$sectionTitle" :noindex="true">
    <section class="container-editorial flex min-h-[60vh] flex-col items-center justify-center py-28 text-center">
        <p class="eyebrow mb-6">RodSant Store</p>
        <h1 class="max-w-2xl font-serif text-4xl font-medium leading-tight text-noir sm:text-6xl">
            {{ $sectionTitle }}
        </h1>
        <p class="mt-6 max-w-md text-base font-light leading-relaxed text-ash">
            Estamos preparando esta sección con el mismo cuidado que ponemos en cada prenda.
            Vuelve muy pronto.
        </p>
        <a href="{{ route('home') }}" class="btn-outline mt-12">
            Volver al inicio
        </a>
    </section>
</x-layouts.storefront>
