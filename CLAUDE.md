# CLAUDE.md — RodSant Store

Guía para trabajar en este repositorio. Léela al iniciar cada sesión.

## Qué es

E-commerce de **RodSant Store**, marca de **ropa deportiva premium para mujer** (sportswear). El **sitio** tiene estética monocromática B&N minimalista (tipografía Archivo + Inter), pero **las prendas pueden tener color** — el copy NO debe decir que la ropa es solo blanco y negro. Tono de rendimiento/movimiento, dirigido a la mujer activa. Inspirado en la UX de DMaria Store. El checkout se cierra **por WhatsApp** (crea un pedido real en BD y genera un mensaje a `wa.me`); la pasarela de pago en línea queda para una fase posterior.

**Restricción #1 (obligatoria):** la propietaria NO es técnica. El panel admin debe permitir gestionar TODA la tienda sin tocar código, en español, con lenguaje humano (sin jerga), validaciones amables y publicación automática al guardar. NO generar descripciones con IA por ahora.

## Stack

- **Laravel 12** (12.62) · **PHP 8.2.12** (XAMPP) — la spec pedía 8.4; Laravel 12 corre en 8.2. Escribir código compatible con 8.2.
- **MySQL** (BD `rodsant`) · **Tailwind v4** (config en `resources/css/app.css` vía `@theme`, NO `tailwind.config.js`) · **Livewire 3** (viene transitivo de Filament; no está en `composer.json` directo) · **Alpine.js** (lo carga `@livewireScripts`)
- **Filament v4** (panel admin, `/admin`)
- **spatie/laravel-permission** (roles y permisos)
- **Tipografía de marca:** **Archivo** (títulos/display, font-extrabold uppercase) + **Inter** (cuerpo), vía Google Fonts en el layout. Paleta B&N: tokens `noir/ink/ash/mist/bone/platinum`. Build con **Vite** (`npm run build` / `npm run dev`).

## Entorno

- Plataforma Windows. Shell preferido: PowerShell; también hay Bash (git-bash).
- MySQL de XAMPP: `127.0.0.1:3306`, usuario `root`, sin contraseña. Cliente CLI: `C:\xampp\mysql\bin\mysql`.
- Extensiones PHP habilitadas en `C:\xampp\php\php.ini` (requeridas por Filament): **`intl`** y **`zip`**. Si reinstalas PHP, vuelve a habilitarlas.
- `.env` ya configurado para MySQL + variables propias bajo el prefijo `RODSANT_` (ver `config/rodsant.php`).

## Comandos

```bash
php artisan serve                 # levantar la app → http://localhost:8000/admin
php artisan migrate               # migrar
php artisan migrate:fresh --seed  # recrear BD + datos demo
php artisan test                  # ejecutar tests (sqlite :memory:)
php artisan test --filter=Nombre  # un test concreto
./vendor/bin/pint app/...         # formatear (estándar Laravel) — correr antes de cerrar trabajo
php artisan rodsant:sync-stock-alerts      # reporte de stock bajo/agotado
php artisan rodsant:clean-abandoned-carts  # limpiar carritos abandonados
```

Generadores Filament v4 (usar siempre los stubs, luego personalizar):
```bash
php artisan make:filament-resource Nombre --generate
php artisan make:filament-widget Nombre --stats-overview   # o --table
php artisan make:filament-relation-manager Recurso relacion atributoTitulo
```

## Credenciales demo del panel

- Super Admin: `admin@rodsantstore.com` / `password`
- Administradora: `tienda@rodsantstore.com` / `password`

## Arquitectura y convenciones

- **MVC + capa de Servicios + Repositorios.** Controllers/Livewire/Filament solo orquestan; la lógica vive en `app/Services/`. Consultas complejas/reutilizables en `app/Repositories/`.
- **Enums tipados** en `app/Enums/` para estados y catálogos: `OrderStatus`, `PaymentStatus`, `PaymentMethod`, `StockStatus`, `UserRole`, `BannerType`, `AttributeType`, y **`Permission`** (catálogo central de los 20 permisos — único origen de verdad para seeder/policies/gates).
- **Modelos** (`app/Models/`): `declare(strict_types=1)`, método `casts()` (no propiedad), enums nativos en casts, scopes tipados `Builder<Model>`, accessors/mutators con `Illuminate\Database\Eloquent\Casts\Attribute` donde aportan valor, `$fillable` explícito. `SoftDeletes` en Category/Product/Order. `OrderItem` guarda **snapshot inmutable**.
- **Variantes:** producto → `ProductVariant` (combinación talla×color vía pivote `attribute_product_variant`) → `Inventory` (stock propio). Disponible = `quantity - reserved`. Validar stock al añadir, actualizar y confirmar (anti-sobreventa).
- **Autorización:** policies en `app/Policies/` (auto-descubiertas) mapean a permisos granulares del enum `Permission`. `AuthServiceProvider` define `Gate::before` (bypass total para Super Admin) y el gate `access-admin`. Roles: **Super Admin** (todo), **Administrador** (todo menos `settings.manage` y `users.manage`), **Cliente** (sin permisos de panel; acceso por propiedad).
- **Filament:** los Resources se gatean solos vía Policy. `User implements FilamentUser` → `canAccessPanel()` = `isStaff()`. Estructura v4: `app/Filament/Resources/{Recurso}/` con `ProductResource.php`, `Schemas/`, `Tables/`, `Pages/`, `RelationManagers/`. Layout viene de `Filament\Schemas\Components\*`; campos de `Filament\Forms\Components\*`; `Set`/`Get` de `Filament\Schemas\Components\Utilities\*`; acciones de tabla en `recordActions`/`toolbarActions`.
- **Textos del panel en español**, labels amables (ej. "Mostrar en la tienda" en vez de "is_active").

## Estado del proyecto

**Hecho:**
- Capa de datos completa: migraciones, 20 modelos + relaciones, factories.
- Roles/permisos granulares (Spatie) + policies + gates.
- **Panel Filament v4:** Dashboard (widgets `StatsOverview`, `LatestOrders`, `TopProducts`, **`SalesChart`** — gráfica de barras de ventas con selector semanal/mensual; periodos calculados en PHP para servir igual en MySQL y sqlite) + **Productos** (`ProductResource`, form slug-auto, imágenes reordenables, SEO, `VariantsRelationManager`) + **Configuración** (`app/Filament/Pages/StoreSettings.php`, gated `settings.manage`: número WhatsApp, contacto, redes).
- **Storefront público COMPLETO:** Home; **Catálogo con filtros Livewire** (`/tienda`, `/novedades`, `/ofertas`, `/categoria/{slug}`, `/coleccion/{slug}` → componente `App\Livewire\Storefront\Catalog` + `CatalogService`); **Ficha de producto (PDP)** (`/producto/{slug}` → `ProductController` + `App\Livewire\Storefront\ProductDetail`, galería + variantes + relacionados + JSON-LD); carrito (Livewire); checkout + cierre por WhatsApp; **selector de moneda COP/USD/EUR con banderas SVG** (`components/storefront/flag.blade.php`); **botón flotante de WhatsApp** (`App\Support\Whatsapp` + `whatsapp-float`).
- **Datos demo** vía `database/seeders/DemoSeeder.php` (único que llama `DatabaseSeeder`): 4 categorías (Camisetas Oversize, Hoodies, Cargos, Accesorios), 22 productos con **imágenes placeholder SVG de marca** generadas por el seeder, variantes talla×color, inventario realista (agotados + stock bajo), 3 colecciones, 3 banners (Hero inactivo a propósito → Home usa la fachada `public/img/hero-storefront.jpg`), 14 clientes + 14 pedidos en los 6 estados.
- **EN VIVO en Railway:** https://rodsant-store-production.up.railway.app (tienda) · `/admin` (panel). `Dockerfile` (nginx + php-fpm vía `serversideup/php:8.2-fpm-nginx`), `railway.json` (healthcheck `/up`), trust proxies en `bootstrap/app.php`, guía `DEPLOY-RAILWAY.md`.
  - **`ext-intl` se instala en el Dockerfile** (`USER root` → `install-php-extensions intl` → `USER www-data`): la imagen base NO lo trae y Filament 4 lo exige. NO usar `--ignore-platform-req`.
  - Railway: la red pública debe apuntar al **puerto 8080** (el de la imagen); si no, da 502 aunque el contenedor esté sano. Variables (`APP_KEY`, `APP_URL`, `DB_*` → `${{MySQL.*}}`) en el servicio de la app; datos demo cargados una vez con `php artisan db:seed --force` desde la Console.
- **Diseño/UX del storefront (sesión jun-2026):** Hero rediseñado **lado a lado** (texto + foto, fondo = misma foto muy difuminada `blur-3xl`); **marca de agua del logo RS** fija detrás de toda la página (layout `storefront.blade.php`: filtro SVG `luminanceToAlpha` que recorta el círculo y deja solo las letras teñidas de gris, secciones translúcidas `bg-bone/75`/`bg-noir/82` para que se asome); **animaciones profesionales** (entrada escalonada del hero `.hero-rise`/`.hero-photo-in`, cascada al scroll vía `[data-reveal-group]` en `storefront.js`, elevación+sombra en `product-card`, header que se compacta al bajar). Todo respeta `prefers-reduced-motion`.
- **Auto-deploy:** hook `Stop` en `.claude/settings.local.json` hace `git add -A` + commit + `git push origin main` al terminar cada tarea; Railway redespliega solo con cada push (~3-4 min).
- **36 tests passing** (Home, Catalog/PDP, CheckoutFlow, CurrencySwitcher, AdminPanel, StoreSettings).

**Pendiente (módulos del panel):** Inventario · Categorías · Pedidos (cambio de estados) · Clientes · Banners · Usuarios. (Configuración: solo WhatsApp/contacto/redes; falta el resto de ajustes.)

**Pendiente (storefront):** listado de colecciones (`/colecciones` sigue siendo placeholder "Próximamente"), cuenta, favoritos. Rutas placeholder restantes (`ComingSoonController`): `collections.index`, `account.index`, `wishlist.index`, `page.show`.

**Más adelante:** pasarela de pago en línea (Wompi/PayU), búsqueda (Meilisearch), SEO técnico avanzado, optimización de imágenes (placeholders SVG → fotos reales).

## Documentos de referencia

- `docs/ARQUITECTURA.md` — documento técnico de arquitectura aprobado (10 secciones).
- `DEPLOY-RAILWAY.md` — guía paso a paso para publicar la tienda en Railway (Dockerfile + MySQL + variables + volumen).

## Reglas de trabajo

- Construir **módulo por módulo** y **verificar** (tests o smoke test) antes de dar por terminado.
- Correr **Pint** sobre los archivos tocados antes de cerrar.
- No introducir sintaxis exclusiva de PHP 8.3/8.4 (el runtime es 8.2).
- El copy de la tienda se dirige a la **mujer**; NO afirmar que la ropa es solo blanco y negro (la estética del sitio sí es B&N, las prendas no).
- Imágenes de producto demo = **placeholders SVG** generados por `DemoSeeder`; reemplazar por fotos reales antes de producción.
- Antes de tocar Filament v4, calcar la API de los recursos/páginas existentes (`ProductForm`, `StoreSettings`): `Filament\Schemas\Schema`, `$this->form` + `statePath('data')` en páginas.
