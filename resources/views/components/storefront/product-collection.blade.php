@props([
    'title',
    'products',
    'eyebrow' => null,
    'subtitle' => null,
    'href' => null,
    'linkLabel' => 'Ver todo',
    'dark' => false,
])

@if ($products->isNotEmpty())
    <section class="{{ $dark ? 'bg-noir text-bone' : 'bg-bone' }} py-16 lg:py-24">
        <div class="container-editorial">
            <x-storefront.section-header
                :eyebrow="$eyebrow"
                :title="$title"
                :subtitle="$subtitle"
                :href="$href"
                :link-label="$linkLabel"
                class="reveal {{ $dark ? '[&_h2]:text-bone [&_.eyebrow]:text-bone/50 [&_a]:text-bone' : '' }}"
            />

            <div class="reveal mt-10 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:mt-14 lg:grid-cols-4 lg:gap-x-8 lg:gap-y-14">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
