@php
    $field = 'w-full border bg-white px-4 py-3.5 font-sans text-sm text-noir placeholder:text-ash/70 focus:outline-none focus:ring-0 transition-colors duration-200';
    $ok = 'border-noir/15 focus:border-noir';
    $err = 'border-red-400 focus:border-red-500';
@endphp

<div class="container-editorial py-10 lg:py-16">
    {{-- Cabecera --}}
    <div class="mb-8">
        <a href="{{ route('cart.index') }}" wire:navigate
           class="inline-flex items-center gap-2 font-sans text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-ash transition-colors hover:text-noir">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Volver al carrito
        </a>
        <h1 class="mt-4 font-serif text-3xl font-medium text-noir sm:text-4xl">Finalizar compra</h1>
        <p class="mt-2 font-sans text-sm font-light text-ash">
            Completa tus datos y cerramos el pedido contigo por WhatsApp.
        </p>
    </div>

    @if ($stockError)
        <div class="mb-6 flex items-start gap-3 border border-amber-300 bg-amber-50 px-4 py-3 font-sans text-sm text-amber-800">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div>
                <p class="font-medium">Revisa tu carrito</p>
                <p>{{ $stockError }} <a href="{{ route('cart.index') }}" wire:navigate class="underline">Ajustar carrito</a>.</p>
            </div>
        </div>
    @endif

    <form wire:submit="placeOrder" class="grid gap-10 lg:grid-cols-[1fr_400px] lg:gap-14">
        {{-- ===== Campos ===== --}}
        <div class="space-y-10">
            {{-- Contacto --}}
            <section>
                <div class="mb-5 flex items-center gap-3">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-noir font-sans text-[0.7rem] font-semibold text-bone">1</span>
                    <h2 class="font-serif text-xl font-medium text-noir">Datos de contacto</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="full_name" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Nombre completo *</label>
                        <input id="full_name" type="text" wire:model="full_name" autocomplete="name"
                               class="{{ $field }} {{ $errors->has('full_name') ? $err : $ok }}" placeholder="Ej. Juan Pérez">
                        @error('full_name') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Teléfono / WhatsApp *</label>
                        <input id="phone" type="tel" wire:model="phone" autocomplete="tel" inputmode="tel"
                               class="{{ $field }} {{ $errors->has('phone') ? $err : $ok }}" placeholder="300 000 0000">
                        @error('phone') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Correo electrónico</label>
                        <input id="email" type="email" wire:model="email" autocomplete="email"
                               class="{{ $field }} {{ $errors->has('email') ? $err : $ok }}" placeholder="tu@correo.com">
                        @error('email') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Envío --}}
            <section>
                <div class="mb-5 flex items-center gap-3">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-noir font-sans text-[0.7rem] font-semibold text-bone">2</span>
                    <h2 class="font-serif text-xl font-medium text-noir">Dirección de entrega</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="department" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Departamento *</label>
                        <input id="department" type="text" wire:model="department" autocomplete="address-level1"
                               class="{{ $field }} {{ $errors->has('department') ? $err : $ok }}" placeholder="Ej. Cundinamarca">
                        @error('department') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Ciudad *</label>
                        <input id="city" type="text" wire:model="city" autocomplete="address-level2"
                               class="{{ $field }} {{ $errors->has('city') ? $err : $ok }}" placeholder="Ej. Bogotá">
                        @error('city') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address_line" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Dirección *</label>
                        <input id="address_line" type="text" wire:model="address_line" autocomplete="street-address"
                               class="{{ $field }} {{ $errors->has('address_line') ? $err : $ok }}" placeholder="Calle 00 # 00-00, apto / barrio">
                        @error('address_line') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="references" class="mb-1.5 block font-sans text-[0.7rem] uppercase tracking-[0.14em] text-ash">Referencias de entrega</label>
                        <input id="references" type="text" wire:model="references"
                               class="{{ $field }} {{ $errors->has('references') ? $err : $ok }}" placeholder="Ej. Edificio gris, dejar en portería">
                        @error('references') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Notas --}}
            <section>
                <div class="mb-5 flex items-center gap-3">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-noir font-sans text-[0.7rem] font-semibold text-bone">3</span>
                    <h2 class="font-serif text-xl font-medium text-noir">Notas del pedido</h2>
                </div>
                <textarea id="notes" wire:model="notes" rows="3"
                          class="{{ $field }} {{ $errors->has('notes') ? $err : $ok }} resize-none"
                          placeholder="¿Algo que debamos saber? (opcional)"></textarea>
                @error('notes') <p class="mt-1 font-sans text-xs text-red-600">{{ $message }}</p> @enderror
            </section>
        </div>

        {{-- ===== Resumen + CTA ===== --}}
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="border border-mist bg-bone p-6 lg:p-7">
                <livewire:storefront.checkout.order-summary />

                <button type="submit" wire:loading.attr="disabled" wire:target="placeOrder"
                        class="mt-6 flex w-full items-center justify-center gap-2.5 bg-[#25D366] py-4 font-sans text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-white transition-opacity duration-300 hover:opacity-90 disabled:opacity-60">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" wire:loading.remove wire:target="placeOrder">
                        <path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2Zm5.2 13.9c-.2.6-1.2 1.2-1.7 1.2-.4 0-1 .1-3.4-.9-2.9-1.2-4.7-4.2-4.8-4.4-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 .9-2.2.2-.2.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .6l-.4.6c-.1.2-.3.3-.1.6.5.8 1 1.4 1.7 1.9.6.4 1 .6 1.3.7.2.1.4.1.5-.1l.7-.8c.2-.2.3-.2.6-.1l1.9.9c.3.1.5.2.5.4.1.2.1.9-.1 1.5Z" />
                    </svg>
                    <span wire:loading.remove wire:target="placeOrder">Confirmar pedido</span>
                    <span wire:loading wire:target="placeOrder">Creando pedido…</span>
                </button>

                <p class="mt-4 text-center font-sans text-[0.7rem] leading-relaxed text-ash">
                    Al confirmar, generamos tu pedido y abrimos WhatsApp con el resumen listo para enviar.
                    No se realiza ningún cobro en línea.
                </p>
            </div>
        </aside>
    </form>
</div>
