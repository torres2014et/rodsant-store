<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BannerType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Datos demo completos y realistas para presentar RodSant Store a la clienta.
 *
 * Genera una tienda "poblada": categorías reales, 22 productos de ropa
 * deportiva para mujer con imágenes B&N, variantes talla/color, inventario
 * realista (con agotados y stock bajo), colecciones, banners, clientes y
 * pedidos en distintos estados con fechas repartidas para que el dashboard
 * muestre ventas, más vendidos, pedidos recientes y alertas de stock.
 *
 * Pensado para ejecutarse con `php artisan migrate:fresh --seed` (canónico),
 * pero es razonablemente idempotente vía updateOrCreate.
 */
class DemoSeeder extends Seeder
{
    /** @var array<string, AttributeValue> */
    private array $sizeMap = [];

    /** @var array<string, AttributeValue> */
    private array $colorMap = [];

    /** @var array<int, Product> */
    private array $products = [];

    public function run(): void
    {
        $this->loadAttributeValues();

        if ($this->sizeMap === [] || $this->colorMap === []) {
            $this->command?->warn('DemoSeeder: faltan atributos talla/color. Ejecuta AttributeSeeder primero.');

            return;
        }

        $this->seedCategories();
        $this->seedProducts();
        $this->seedCollections();
        $this->seedBanners();
        $this->seedOrders();
    }

    private function loadAttributeValues(): void
    {
        $tallas = Attribute::query()->where('slug', 'talla')->first()?->values()->get() ?? collect();
        $colores = Attribute::query()->where('slug', 'color')->first()?->values()->get() ?? collect();

        foreach ($tallas as $value) {
            $this->sizeMap[$value->value] = $value;
        }
        foreach ($colores as $value) {
            $this->colorMap[$value->value] = $value;
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Camisetas Oversize', 'image' => 'demo/categories/camisetas.jpg'],
            ['name' => 'Hoodies', 'image' => 'demo/categories/hoodies.jpg'],
            ['name' => 'Cargos', 'image' => 'demo/categories/cargos.jpg'],
            ['name' => 'Accesorios', 'image' => 'demo/categories/accesorios.jpg'],
        ];

        foreach ($categories as $position => $data) {
            $slug = Str::slug($data['name']);
            $image = 'demo/categories/'.$slug.'.svg';
            $this->writePlaceholder($image, 'dark', null, '', 800, 1000);

            Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'image' => $image,
                    'position' => $position,
                    'gender' => 'mujer',
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productCatalog(): array
    {
        // stock: healthy | low | out | mixed   ·   colors / sizes opcionales
        return [
            // ── Camisetas Oversize ──────────────────────────────────────────
            ['Camiseta Oversize Motion', 'camisetas-oversize', 89900, null, true, true, ['Negro', 'Blanco'], 'healthy',
                'Corte oversize que se mueve contigo.',
                'Camiseta oversize de algodón técnico con tacto suave y caída relajada. Libertad total para entrenar o para tu día a día, con el sello monocromático de RodSant.'],
            ['Camiseta Oversize Core', 'camisetas-oversize', 79900, null, false, false, ['Negro', 'Gris'], 'healthy',
                'La básica que nunca falla.',
                'Una esencial de tu clóset deportivo: ligera, transpirable y con un calce oversize favorecedor. Combina con todo.'],
            ['Camiseta Oversize Active', 'camisetas-oversize', 95900, null, false, true, ['Negro', 'Blanco', 'Gris'], 'mixed',
                'Frescura y movimiento en cada repetición.',
                'Tejido de secado rápido que mantiene la frescura durante el entrenamiento. Costuras planas para evitar roces y máxima comodidad.'],
            ['Camiseta Oversize Mono', 'camisetas-oversize', 99900, 79900, false, false, ['Negro', 'Hueso'], 'low',
                'Edición monocromo en oferta.',
                'Diseño limpio en blanco y negro con acabado mate. Algodón premium de gramaje medio para una caída perfecta.'],
            ['Camiseta Oversize Flow', 'camisetas-oversize', 99900, null, true, false, ['Blanco', 'Gris'], 'healthy',
                'Suave, fluida, hecha para ti.',
                'Tejido fluido que acompaña cada gesto. Ideal para yoga, pilates o un look athleisure sin esfuerzo.'],
            ['Top Oversize Breath', 'camisetas-oversize', 92900, null, false, false, ['Negro', 'Blanco'], 'out',
                'Transpirabilidad de alto rendimiento.',
                'Top oversize con zonas de ventilación estratégicas. Te mantiene fresca en los entrenamientos más exigentes.'],

            // ── Hoodies ─────────────────────────────────────────────────────
            ['Hoodie Essential Noir', 'hoodies', 189900, null, true, true, ['Negro', 'Gris'], 'healthy',
                'El hoodie que vas a querer todos los días.',
                'Hoodie de felpa suave por dentro y acabado premium por fuera. Capucha forrada y bolsillo canguro. Abrigo y estilo en una sola prenda.'],
            ['Hoodie Cropped Move', 'hoodies', 169900, null, false, true, ['Negro', 'Hueso'], 'healthy',
                'Corte cropped para un look actual.',
                'Hoodie cropped con puños elásticos y caída moderna. Perfecto sobre un top deportivo y leggings de tiro alto.'],
            ['Hoodie Oversize Studio', 'hoodies', 199900, null, true, false, ['Gris', 'Blanco'], 'mixed',
                'Comodidad oversize de estudio a calle.',
                'Felpa de algodón orgánico con interior cepillado. Silueta oversize envolvente para esos días de máxima comodidad.'],
            ['Hoodie Zip Performance', 'hoodies', 249900, 219900, false, false, ['Negro', 'Blanco'], 'healthy',
                'Cierre completo, rendimiento total.',
                'Hoodie con cremallera completa y tejido técnico que regula la temperatura. Tu mejor aliado para el calentamiento y la vuelta a la calma.'],
            ['Hoodie Soft Touch', 'hoodies', 179900, null, false, false, ['Negro', 'Gris'], 'low',
                'Tacto suave que abraza.',
                'Mezcla ultrasuave de algodón y modal. Ligero pero cálido, con un acabado que se siente increíble sobre la piel.'],

            // ── Cargos ──────────────────────────────────────────────────────
            ['Cargo Jogger Flex', 'cargos', 159900, null, true, true, ['Negro', 'Gris'], 'healthy',
                'Cargo jogger con elasticidad real.',
                'Pantalón cargo con tejido elástico en 4 direcciones y bolsillos laterales funcionales. Tiro alto que estiliza y puños ajustables.'],
            ['Cargo Wide Move', 'cargos', 169900, null, false, false, ['Negro', 'Hueso'], 'healthy',
                'Pierna ancha, libertad total.',
                'Cargo de pierna ancha y caída fluida. Comodidad de athleisure con actitud urbana para llevar a todas partes.'],
            ['Cargo Slim Active', 'cargos', 149900, null, false, true, ['Negro', 'Blanco'], 'healthy',
                'Slim fit que marca tu silueta.',
                'Corte slim con tejido técnico ligero y resistente. Ideal para entrenar y seguir tu día sin cambiarte.'],
            ['Cargo Tech Noir', 'cargos', 179900, null, true, false, ['Negro', 'Gris'], 'low',
                'Tecnología y diseño en negro absoluto.',
                'Cargo técnico con acabado repelente al agua y costuras reforzadas. Detalles minimalistas y máxima funcionalidad.'],
            ['Cargo Comfort', 'cargos', 159900, 139900, false, false, ['Negro', 'Hueso'], 'healthy',
                'Suavidad de felpa, ahora en oferta.',
                'Cargo de felpa premium con interior suave. La comodidad de un jogger con el estilo de un cargo.'],

            // ── Accesorios ──────────────────────────────────────────────────
            ['Gorra RodSant Mono', 'accesorios', 59900, null, false, true, ['Negro', 'Blanco'], 'healthy',
                'Remate perfecto para tu look deportivo.',
                'Gorra de algodón con bordado RodSant y cierre ajustable. Talla única, monocromática.', 'color'],
            ['Riñonera Active', 'accesorios', 79900, null, true, false, ['Negro', 'Gris'], 'healthy',
                'Lo esencial siempre contigo.',
                'Riñonera compacta y resistente al agua, con correa ajustable. Perfecta para entrenar ligera o salir sin cargar de más.', 'color'],
            ['Medias Performance (Pack x3)', 'accesorios', 39900, null, false, false, [], 'healthy',
                'Pack de 3 pares de alto rendimiento.',
                'Medias deportivas con soporte de arco y zonas de ventilación. Tejido que absorbe la humedad para mantener tus pies secos.', 'none'],
            ['Botella Térmica RS', 'accesorios', 69900, null, false, false, ['Negro', 'Blanco'], 'healthy',
                'Hidratación con estilo.',
                'Botella térmica de acero inoxidable de 600 ml. Mantiene la temperatura por horas. Imprescindible en tu rutina.', 'color'],
            ['Mochila Studio', 'accesorios', 119900, null, true, true, ['Negro', 'Gris'], 'mixed',
                'Del gym al estudio, todo en orden.',
                'Mochila resistente con compartimento para portátil, bolsillo para zapatos y correas acolchadas. Diseño limpio y funcional.', 'color'],
            ['Banda Deportiva (Pack x2)', 'accesorios', 39900, 29900, false, false, [], 'out',
                'Sujeción perfecta, pack de 2.',
                'Bandas elásticas antideslizantes para el cabello. Se mantienen en su lugar durante el entrenamiento más intenso.', 'none'],
        ];
    }

    private function seedProducts(): void
    {
        foreach ($this->productCatalog() as $i => $row) {
            [$name, $catSlug, $base, $sale, $featured, $new, $colors, $stock, $short, $desc] = $row;
            $variantMode = $row[10] ?? 'apparel'; // apparel | color | none

            $index = $i + 1;
            $category = Category::query()->where('slug', $catSlug)->first();
            if ($category === null) {
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'sku' => 'RS-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'short_description' => $short,
                    'description' => $desc,
                    'base_price' => $base,
                    'sale_price' => $sale,
                    'is_active' => true,
                    'is_featured' => $featured,
                    'is_new' => $new,
                    'views' => random_int(120, 4200),
                    'meta_title' => $name.' · RodSant Store',
                    'meta_description' => $short,
                    'position' => $index,
                ],
            );

            // Reconstruir variantes/inventario/imágenes (idempotente).
            foreach ($product->variants()->get() as $oldVariant) {
                $oldVariant->inventory()->delete();
                $oldVariant->attributeValues()->detach();
                $oldVariant->delete();
            }
            $product->images()->delete();

            $this->buildVariants($product, $variantMode, $colors, $stock, $index);
            $this->buildImages($product, $index, $category->name);

            $this->products[$index] = $product;
        }
    }

    /**
     * @param  list<string>  $colors
     */
    private function buildVariants(Product $product, string $mode, array $colors, string $stock, int $index): void
    {
        $sizes = array_keys($this->sizeMap);

        $combos = [];
        if ($mode === 'apparel') {
            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $combos[] = [$color, $size];
                }
            }
        } elseif ($mode === 'color') {
            foreach ($colors as $color) {
                $combos[] = [$color, null];
            }
        } else { // none
            $combos[] = [null, null];
        }

        foreach ($combos as $pos => [$color, $size]) {
            $skuParts = ['RS', str_pad((string) $index, 3, '0', STR_PAD_LEFT)];
            if ($color !== null) {
                $skuParts[] = mb_strtoupper(mb_substr($color, 0, 1));
            }
            if ($size !== null) {
                $skuParts[] = $size;
            }

            $variant = ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => implode('-', $skuParts).'-'.$pos,
                'is_active' => true,
            ]);

            $attach = [];
            if ($color !== null && isset($this->colorMap[$color])) {
                $attach[] = $this->colorMap[$color]->id;
            }
            if ($size !== null && isset($this->sizeMap[$size])) {
                $attach[] = $this->sizeMap[$size]->id;
            }
            if ($attach !== []) {
                $variant->attributeValues()->attach($attach);
            }

            Inventory::query()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $this->stockQuantity($stock, $pos),
                'reserved' => 0,
                'low_stock_threshold' => 3,
            ]);
        }
    }

    private function stockQuantity(string $profile, int $pos): int
    {
        return match ($profile) {
            'out' => 0,
            'low' => random_int(1, 3),
            'mixed' => $pos % 4 === 0 ? 0 : ($pos % 3 === 0 ? random_int(1, 3) : random_int(10, 35)),
            default => random_int(8, 40),
        };
    }

    private function buildImages(Product $product, int $index, string $categoryName): void
    {
        $base = sprintf('demo/products/p%02d', $index);

        // Placeholder de marca: tarjeta clara con el nombre (principal) y
        // tarjeta oscura con monograma RS (vista al pasar el cursor).
        $this->writePlaceholder($base.'a.svg', 'light', $categoryName, $product->name);
        $this->writePlaceholder($base.'b.svg', 'dark', null, '');

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => $base.'a.svg',
            'alt' => $product->name,
            'position' => 0,
            'is_primary' => true,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => $base.'b.svg',
            'alt' => $product->name.' — vista alterna',
            'position' => 1,
            'is_primary' => false,
        ]);
    }

    private function seedCollections(): void
    {
        $collections = [
            ['Esenciales Mono', 'col1.jpg', 'Las prendas que no pueden faltar en tu clóset deportivo.', [1, 2, 5, 7, 12, 14], false],
            ['Active Studio', 'col2.jpg', 'Diseñadas para el estudio y para la calle, sin renunciar al estilo.', [9, 11, 18, 21, 7], false],
            ['Nueva Temporada', 'col3.jpg', 'Lo último en llegar a RodSant. Recién desempacado para ti.', [1, 3, 8, 14, 17], true],
        ];

        foreach ($collections as $position => [$name, $img, $desc, $productIdx, $isNew]) {
            $slug = Str::slug($name);
            $cover = 'demo/collections/'.$slug.'.svg';
            // Sin título centrado: la tarjeta superpone el nombre de la colección.
            $this->writePlaceholder($cover, 'dark', null, '', 1200, 1500);

            $collection = ProductCollection::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'cover_image' => $cover,
                    'description' => $desc,
                    'launches_at' => $isNew ? Carbon::now()->subDays(2) : Carbon::now()->subMonths($position + 1),
                    'is_active' => true,
                ],
            );

            $sync = [];
            foreach ($productIdx as $pos => $idx) {
                if (isset($this->products[$idx])) {
                    $sync[$this->products[$idx]->id] = ['position' => $pos];
                }
            }
            $collection->products()->sync($sync);
        }
    }

    private function seedBanners(): void
    {
        // [type, title, subtitle, file, link, cta, position, activo, w, h]
        // El Hero queda INACTIVO a propósito: el Home conserva la fachada
        // editorial del local (public/img/hero-storefront.jpg). La clienta
        // puede subir su propia foto y activarlo desde el panel (a futuro).
        $banners = [
            [BannerType::Hero, 'Muévete sin límites', 'Ropa deportiva premium para mujer',
                'demo/banners/hero.svg', '/tienda', 'Explorar la tienda', 0, false, 1600, 900],
            [BannerType::Strip, 'Hasta 20% en selección', 'Por tiempo limitado · Esenciales y novedades',
                'demo/banners/promo.svg', '/ofertas', 'Ver ofertas', 1, true, 1600, 700],
            [BannerType::Collection, 'Nueva Temporada', 'Recién llegado a RodSant',
                'demo/banners/coleccion.svg', '/coleccion/nueva-temporada', 'Descubrir', 2, true, 1200, 1500],
        ];

        foreach ($banners as [$type, $title, $subtitle, $img, $link, $cta, $position, $active, $w, $h]) {
            $this->writePlaceholder($img, 'dark', $subtitle, $title, $w, $h);

            Banner::query()->updateOrCreate(
                ['type' => $type->value, 'title' => $title],
                [
                    'subtitle' => $subtitle,
                    'image_desktop' => $img,
                    'image_mobile' => $img,
                    'link_url' => $link,
                    'cta_label' => $cta,
                    'position' => $position,
                    'is_active' => $active,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            );
        }
    }

    /**
     * Crea un placeholder de marca en SVG (B&N) y lo guarda en storage/public.
     */
    private function writePlaceholder(string $relPath, string $theme, ?string $eyebrow, string $title, int $w = 800, int $h = 1000): void
    {
        $abs = storage_path('app/public/'.$relPath);
        $dir = dirname($abs);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if ($theme === 'dark') {
            [$bg1, $bg2, $fg] = ['#0A0A0A', '#1A1A1A', '#F6F4F1'];
        } else {
            [$bg1, $bg2, $fg] = ['#F6F4F1', '#E4E1DD', '#0A0A0A'];
        }

        $cx = $w / 2;
        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">';
        $svg[] = '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            .'<stop offset="0" stop-color="'.$bg1.'"/><stop offset="1" stop-color="'.$bg2.'"/></linearGradient></defs>';
        $svg[] = '<rect width="'.$w.'" height="'.$h.'" fill="url(#g)"/>';
        // Monograma RS tenue de fondo.
        $svg[] = '<text x="'.$cx.'" y="'.(int) ($h * 0.62).'" font-family="Archivo, Arial, sans-serif" font-size="'.(int) ($w * 0.5).'" font-weight="800" fill="'.$fg.'" fill-opacity="0.06" text-anchor="middle">RS</text>';
        // Marco fino.
        $svg[] = '<rect x="'.(int) ($w * 0.05).'" y="'.(int) ($h * 0.04).'" width="'.(int) ($w * 0.9).'" height="'.(int) ($h * 0.92).'" fill="none" stroke="'.$fg.'" stroke-opacity="0.12" stroke-width="2"/>';

        if ($title !== '') {
            $lines = $this->wrapText($title, 14, 3);
            $count = count($lines);
            $fontSize = $count >= 3 ? (int) ($w * 0.05) : ($count === 2 ? (int) ($w * 0.062) : (int) ($w * 0.078));
            $lineH = (int) ($fontSize * 1.15);
            $startY = (int) ($h / 2 - ($count - 1) * $lineH / 2 + $fontSize * 0.35);

            if ($eyebrow !== null && $eyebrow !== '') {
                $svg[] = '<text x="'.$cx.'" y="'.($startY - $lineH - 22).'" font-family="Arial, sans-serif" font-size="'.(int) ($w * 0.026).'" letter-spacing="5" fill="'.$fg.'" fill-opacity="0.5" text-anchor="middle">'.$this->esc(mb_strtoupper($eyebrow)).'</text>';
            }
            foreach ($lines as $i => $line) {
                $svg[] = '<text x="'.$cx.'" y="'.($startY + $i * $lineH).'" font-family="Archivo, Arial, sans-serif" font-size="'.$fontSize.'" font-weight="800" letter-spacing="1" fill="'.$fg.'" text-anchor="middle">'.$this->esc(mb_strtoupper($line)).'</text>';
            }
        }

        $svg[] = '<text x="'.$cx.'" y="'.(int) ($h * 0.93).'" font-family="Arial, sans-serif" font-size="'.(int) ($w * 0.024).'" letter-spacing="8" fill="'.$fg.'" fill-opacity="0.55" text-anchor="middle">RODSANT</text>';
        $svg[] = '</svg>';

        file_put_contents($abs, implode("\n", $svg));
    }

    /**
     * Parte un texto en líneas para centrarlo en el SVG.
     *
     * @return list<string>
     */
    private function wrapText(string $text, int $maxChars, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $try = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($try) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $try;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, $maxLines);
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function seedOrders(): void
    {
        if (Order::query()->count() > 0) {
            return; // Evita duplicar pedidos en re-ejecuciones.
        }

        // Pool de variantes con stock para construir líneas realistas.
        /** @var Collection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->with(['product', 'inventory'])
            ->whereHas('inventory', fn ($q) => $q->whereRaw('quantity - reserved > 0'))
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        $orders = [
            ['Valentina Ríos', '3104567890', 'Bogotá', 'Cundinamarca', OrderStatus::Delivered, 40],
            ['Mariana López', '3127894561', 'Medellín', 'Antioquia', OrderStatus::Delivered, 32],
            ['Camila Torres', '3159874562', 'Cali', 'Valle del Cauca', OrderStatus::Delivered, 25],
            ['Daniela Gómez', '3001239874', 'Barranquilla', 'Atlántico', OrderStatus::Shipped, 12],
            ['Sara Martínez', '3112223344', 'Bogotá', 'Cundinamarca', OrderStatus::Delivered, 8],
            ['Laura Jiménez', '3145556677', 'Bucaramanga', 'Santander', OrderStatus::Confirmed, 5],
            ['Andrea Castro', '3178889900', 'Pereira', 'Risaralda', OrderStatus::Confirmed, 3],
            ['Paula Vargas', '3019998877', 'Cartagena', 'Bolívar', OrderStatus::Paid, 2],
            ['Natalia Rojas', '3134445566', 'Manizales', 'Caldas', OrderStatus::Shipped, 2],
            ['Isabella Díaz', '3167778899', 'Bogotá', 'Cundinamarca', OrderStatus::Confirmed, 1],
            ['Juliana Peña', '3102345678', 'Medellín', 'Antioquia', OrderStatus::Pending, 1],
            ['Sofía Herrera', '3156667788', 'Cali', 'Valle del Cauca', OrderStatus::Pending, 0],
            ['Gabriela Mora', '3189990011', 'Bogotá', 'Cundinamarca', OrderStatus::Pending, 0],
            ['Carolina Ruiz', '3041112233', 'Cúcuta', 'Norte de Santander', OrderStatus::Cancelled, 6],
        ];

        $sequence = 1;
        foreach ($orders as [$name, $phone, $city, $dept, $status, $daysAgo]) {
            $createdAt = Carbon::now()->subDays($daysAgo)->setTime(random_int(9, 20), random_int(0, 59));

            $customer = Customer::query()->create([
                'full_name' => $name,
                'email' => Str::slug($name, '.').'@example.com',
                'phone' => $phone,
                'accepts_marketing' => (bool) random_int(0, 1),
            ]);

            $address = Address::query()->create([
                'customer_id' => $customer->id,
                'full_name' => $name,
                'phone' => $phone,
                'department' => $dept,
                'city' => $city,
                'address_line' => 'Calle '.random_int(1, 120).' # '.random_int(1, 80).'-'.random_int(1, 99),
                'references' => null,
                'is_default' => true,
            ]);

            // 1 a 3 líneas por pedido.
            $lineCount = random_int(1, 3);
            $chosen = $variants->random(min($lineCount, $variants->count()));
            if ($chosen instanceof ProductVariant) {
                $chosen = collect([$chosen]);
            }

            $subtotal = 0.0;
            $lines = [];
            foreach ($chosen as $variant) {
                $qty = random_int(1, 3);
                $unit = (float) $variant->price();
                $lineTotal = $unit * $qty;
                $subtotal += $lineTotal;
                $lines[] = [
                    'variant' => $variant,
                    'qty' => $qty,
                    'unit' => $unit,
                    'lineTotal' => $lineTotal,
                ];
            }

            $isPaid = in_array($status, [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered], true);

            $order = Order::query()->create([
                'order_number' => sprintf('RS-%d-%s', $createdAt->year, str_pad((string) $sequence, 6, '0', STR_PAD_LEFT)),
                'customer_id' => $customer->id,
                'status' => $status,
                'payment_status' => $isPaid ? PaymentStatus::Paid : PaymentStatus::Pending,
                'payment_method' => PaymentMethod::Whatsapp,
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'shipping_total' => 0,
                'grand_total' => $subtotal,
                'shipping_address_id' => $address->id,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_email' => $customer->email,
                'notes' => null,
                'whatsapp_sent_at' => $status === OrderStatus::Pending ? null : $createdAt,
            ]);

            $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            foreach ($lines as $line) {
                $item = OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $line['variant']->id,
                    'product_name' => $line['variant']->product->name,
                    'variant_label' => $line['variant']->label() ?: null,
                    'unit_price' => $line['unit'],
                    'quantity' => $line['qty'],
                    'line_total' => $line['lineTotal'],
                ]);
                $item->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
            }

            $sequence++;
        }
    }
}
