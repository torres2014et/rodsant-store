# CLAUDE.md — RodSant Store

Guía para trabajar en este repositorio. Léela al iniciar cada sesión.

## Qué es

E-commerce de **RodSant Store**, marca de **ropa deportiva premium / sportswear** (estética monocromática B&N, minimalista, tono de rendimiento/movimiento; tipografía Archivo + Inter). Inspirado en la UX de DMaria Store. El checkout se cierra **por WhatsApp** (crea un pedido real en BD y genera un mensaje a `wa.me`); la pasarela de pago en línea queda para una fase posterior.

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
- Capa de datos completa: 23 migraciones, 20 modelos + relaciones, factories, seeders (roles, admin, atributos talla/color, categorías, settings, zonas de envío, 24 productos demo).
- Roles/permisos granulares (Spatie) + policies + gates.
- Filament v4: panel configurado + **Dashboard** (widgets `StatsOverview`, `LatestOrders`, `TopProducts`) + **Productos** (`ProductResource` con form slug-auto, imágenes reordenables con principal, SEO; `VariantsRelationManager` talla/color/SKU/stock).

**Pendiente (módulos del panel):** Inventario · Categorías · Pedidos (cambio de estados) · Clientes · Banners · Configuración · Usuarios.

**Más adelante (fuera del panel):** frontend público (Home, catálogo, PDP), carrito (Livewire), checkout + flujo WhatsApp, pasarela de pago en línea, búsqueda (Meilisearch), SEO técnico.

## Documentos de referencia

- `docs/ARQUITECTURA.md` — documento técnico de arquitectura aprobado (10 secciones).

## Reglas de trabajo

- No generar frontend público, carrito ni flujo WhatsApp hasta que se complete y apruebe el panel admin.
- Construir **módulo por módulo** y **verificar** (tests o smoke test) antes de dar por terminado.
- Correr **Pint** sobre los archivos tocados antes de cerrar.
- No introducir sintaxis exclusiva de PHP 8.3/8.4 (el runtime es 8.2).
