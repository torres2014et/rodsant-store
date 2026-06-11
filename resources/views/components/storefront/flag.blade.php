@props(['code' => 'COP'])

@php
    // Mapea el código de moneda a su bandera. Se usan SVG (no emoji) porque
    // Windows no renderiza los emoji de bandera.
    $country = match (strtoupper($code)) {
        'USD' => 'us',
        'EUR' => 'eu',
        default => 'co',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block h-3.5 w-5 shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10 align-middle']) }}>
    @switch($country)
        @case('us')
            <svg viewBox="0 0 21 15" class="h-full w-full" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <rect width="21" height="15" fill="#fff"/>
                @for ($i = 0; $i < 7; $i++)
                    <rect y="{{ round($i * 2 * 15 / 13, 3) }}" width="21" height="{{ round(15 / 13, 3) }}" fill="#B22234"/>
                @endfor
                <rect width="9" height="{{ round(7 * 15 / 13, 3) }}" fill="#3C3B6E"/>
                @for ($r = 0; $r < 4; $r++)
                    @for ($c = 0; $c < 5; $c++)
                        <circle cx="{{ 1 + $c * 1.9 + ($r % 2) * 0.95 }}" cy="{{ 1 + $r * 1.95 }}" r="0.42" fill="#fff"/>
                    @endfor
                @endfor
            </svg>
            @break

        @case('eu')
            <svg viewBox="0 0 21 15" class="h-full w-full" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <rect width="21" height="15" fill="#003399"/>
                @for ($i = 0; $i < 12; $i++)
                    @php
                        $a = deg2rad($i * 30 - 90);
                        $sx = 10.5 + 4.6 * cos($a);
                        $sy = 7.5 + 4.6 * sin($a);
                    @endphp
                    <circle cx="{{ round($sx, 2) }}" cy="{{ round($sy, 2) }}" r="0.8" fill="#FFCC00"/>
                @endfor
            </svg>
            @break

        @default
            {{-- Colombia: 1/2 amarillo, 1/4 azul, 1/4 rojo --}}
            <svg viewBox="0 0 21 15" class="h-full w-full" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <rect width="21" height="7.5" fill="#FCD116"/>
                <rect y="7.5" width="21" height="3.75" fill="#003893"/>
                <rect y="11.25" width="21" height="3.75" fill="#CE1126"/>
            </svg>
    @endswitch
</span>
