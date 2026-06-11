@php
    use App\Support\Whatsapp;

    $waLink = Whatsapp::isConfigured()
        ? Whatsapp::link('¡Hola RodSant! Estoy en la tienda y tengo una pregunta 🙂')
        : null;
@endphp

@if ($waLink)
    <a
        href="{{ $waLink }}"
        target="_blank"
        rel="noopener"
        x-data="{ show: false }"
        x-init="setTimeout(() => show = true, 600)"
        x-show="show"
        x-transition.scale.duration.300ms
        class="group fixed bottom-5 right-5 z-40 flex items-center gap-0 rounded-full bg-[#25D366] py-3 pl-3 pr-3 text-white shadow-[0_10px_30px_-6px_rgba(37,211,102,0.6)] transition-all duration-300 hover:gap-2 hover:pr-5 print:hidden"
        aria-label="Escríbenos por WhatsApp"
    >
        <svg class="h-7 w-7 shrink-0" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
            <path d="M16.04 4C9.96 4 5.02 8.94 5.02 15.02c0 1.94.51 3.84 1.48 5.52L4.9 27l6.62-1.56a11 11 0 0 0 4.52.97h.01c6.08 0 11.02-4.94 11.02-11.02C27.07 8.94 22.12 4 16.04 4Zm0 20.2h-.01a9.13 9.13 0 0 1-4.65-1.27l-.33-.2-3.93.93.94-3.83-.22-.34a9.1 9.1 0 0 1-1.4-4.85c0-5.05 4.11-9.16 9.17-9.16 2.45 0 4.75.96 6.48 2.69a9.1 9.1 0 0 1 2.68 6.48c0 5.06-4.11 9.17-9.16 9.17Zm5.02-6.86c-.27-.14-1.63-.8-1.88-.9-.25-.09-.43-.13-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.59.07-.27-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.9-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47l-.53-.01c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.3 0 1.36.99 2.67 1.12 2.85.14.18 1.94 2.96 4.7 4.15.66.28 1.17.45 1.57.58.66.21 1.26.18 1.74.11.53-.08 1.63-.67 1.86-1.31.23-.64.23-1.19.16-1.31-.07-.12-.25-.18-.52-.32Z"/>
        </svg>
        <span class="max-w-0 overflow-hidden whitespace-nowrap font-sans text-[0.78rem] font-semibold opacity-0 transition-all duration-300 group-hover:max-w-[8rem] group-hover:opacity-100">
            Escríbenos
        </span>
    </a>
@endif
