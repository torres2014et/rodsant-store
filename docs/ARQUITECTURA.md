# Documento de Arquitectura Técnica — RodSant Store

**E-commerce Luxury Streetwear**
Stack: Laravel 12 · PHP 8.4 · MySQL · Tailwind CSS · Livewire · Alpine.js
Versión 1.0 — para revisión y aprobación previa al desarrollo
Autor: Arquitectura de software · Fecha: 2026-06-11

---

## Decisiones por defecto asumidas (🔧 confirmables)

| # | Decisión | Default propuesto |
|---|---|---|
| 1 | Segmentación de catálogo | **Unisex con filtro de género** opcional |
| 2 | Pago en lanzamiento | **WhatsApp + contraentrega/transferencia** (pasarela en línea en fase posterior) |
| 3 | Base del panel admin | **Filament** |
| 4 | Roles | **Super Admin / Administrador / Cliente** |

---

# 1. Arquitectura General

## 1.1 Arquitectura propuesta

**Monolito modular** con patrón **MVC + capa de Servicios + Repositorios**, renderizado en
servidor (SSR) con **islas reactivas** (Livewire/Alpine). No es una SPA.

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE (navegador / móvil)                                 │
│  Blade SSR · Livewire (AJAX reactivo) · Alpine · Tailwind    │
└───────────────────────────────┬─────────────────────────────┘
                                 │ HTTP
┌───────────────────────────────▼─────────────────────────────┐
│  APLICACIÓN — Laravel 12 / PHP 8.4                            │
│                                                               │
│  Presentación:  Controllers · Livewire · Filament (Admin)    │
│         │                                                     │
│  Negocio:       SERVICES (orquestación, reglas)              │
│         │                                                     │
│  Acceso datos:  REPOSITORIES → Eloquent (Models)             │
│         │                                                     │
│  Soporte:  Policies · Requests · Events/Listeners · Jobs ·   │
│            Notifications · Enums · Cache · Queue              │
└───────────┬──────────────────────────────┬──────────────────┘
            │                               │
     ┌──────▼──────┐               ┌────────▼─────────┐
     │   MySQL     │               │  Integraciones    │
     │  InnoDB     │               │  WhatsApp · Email │
     └─────────────┘               │  Pasarela 🔧·Pixel│
     ┌─────────────┐               └──────────────────┘
     │ Meilisearch │ (búsqueda, fase posterior)
     │ Storage/CDN │ (imágenes)
     └─────────────┘
```

**Capas y responsabilidad:**

- **Controllers / Livewire / Filament** → solo orquestan la petición; sin lógica de negocio.
- **Services** → reglas de negocio (crear pedido, calcular carrito, generar mensaje WhatsApp).
- **Repositories** → encapsulan consultas Eloquent complejas y reutilizables; el resto usa Eloquent directo.
- **Models** → relaciones, casts, scopes.

## 1.2 Justificación técnica

| Decisión | Por qué |
|---|---|
| **Monolito modular (no microservicios)** | El volumen no justifica la complejidad operativa de microservicios; el monolito es más rápido de construir, desplegar y mantener por un equipo pequeño. |
| **SSR + Livewire (no SPA/React)** | SEO superior (crítico en e-commerce), menos JavaScript, menor complejidad, time-to-market más corto. Reactividad donde se necesita (carrito, filtros) sin un frontend separado. |
| **Capa de Servicios + Repositorios** | Lógica testeable y reutilizable; desacopla la UI de las reglas; facilita cambiar pasarela de pago o motor de búsqueda sin tocar controladores. |
| **Filament para admin** | Panel profesional, en español, sin código para la dueña; ahorra ~70% del esfuerzo del backoffice. |
| **MySQL/InnoDB** | Transaccional (ACID), ideal para pedidos/stock; ecosistema maduro con Laravel. |
| **Enums PHP 8.4** | Estados tipados (pedido, pago, rol) → menos errores que strings sueltos. |

## 1.3 Escalabilidad

- **Vertical primero** (más CPU/RAM), suficiente para el arranque.
- **Horizontal preparado:** sesiones y caché en **Redis**, colas en Redis/Database, app sin estado → permite múltiples instancias detrás de un balanceador.
- **Lectura intensiva** (catálogo) servida con **cache** y, a futuro, **réplicas de lectura** de MySQL.
- **Búsqueda** desacoplada (Meilisearch) cuando el catálogo crezca.
- **Imágenes** en **Storage + CDN** (no en el servidor de la app).
- **`PaymentGateway` como interface** → nuevas pasarelas sin reescritura.
- **Atributos/variantes extensibles** → entran sneakers, gorras, etc. sin migración estructural.

## 1.4 Seguridad

- **Autenticación**: Laravel (hash bcrypt/argon2), verificación de email, *rate limiting* en login, reset por correo.
- **Autorización**: roles y permisos (spatie) + **Policies** por modelo (un cliente solo ve sus pedidos).
- **CSRF** en formularios; **XSS** mitigado por Blade (escape por defecto); **SQL injection** evitado por Eloquent/bindings.
- **Validación** centralizada en **Form Requests** (servidor) — nunca confiar en el cliente.
- **Mass-assignment** controlado (`$fillable`); **HTTPS** forzado; headers de seguridad (HSTS, X-Frame-Options, CSP).
- **Pagos**: no se almacenan datos de tarjeta; la pasarela (cuando entre) maneja PCI.
- **Backups** automáticos de BD y archivos; **logs** de auditoría en acciones del admin.
- **Subida de imágenes** validada (tipo, tamaño, dimensiones) y renombrada.

## 1.5 Mantenibilidad

- **PSR-12** + **Laravel Pint** (formato) + **Larastan/PHPStan** (análisis estático).
- **Tests** (Pest/PHPUnit): Feature (carrito, checkout, filtros) + Unit (cálculos).
- **Convenciones Laravel** estándar → cualquier desarrollador Laravel se orienta rápido.
- **Capa de servicios** documentada; **Enums** y **config propia** (`config/rodsant.php`) centralizan parámetros.
- **Seeders/Factories** para entornos de prueba; **migraciones** versionadas.

---

# 2. Estructura del Proyecto

```
app/
├── Console/Commands/         CleanAbandonedCarts, SyncStockAlerts
├── Enums/                    OrderStatus, PaymentStatus, PaymentMethod,
│                             StockStatus, UserRole, BannerType
├── Events/                   OrderPlaced, OrderStatusChanged, StockRanLow
├── Listeners/                SendOrderEmails, NotifyAdmin, DecrementStock
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Shop/             Home, Catalog, Product, Page, Search
│   │   ├── Account/          Dashboard, Order, Address, Wishlist
│   │   └── Checkout/         Checkout (WhatsApp)
│   ├── Middleware/           EnsureCartNotEmpty, SetLocale, AdminAccess
│   └── Requests/
│       ├── Checkout/         StoreOrderRequest
│       └── Account/          UpdateProfileRequest, StoreAddressRequest
├── Jobs/                     SendWhatsappNotification, GenerateSitemap,
│                             ProcessProductImages, SendOrderConfirmation
├── Livewire/
│   ├── Shop/                 SearchAutocomplete, CatalogFilters,
│   │                         ProductGallery, VariantSelector,
│   │                         AddToCart, WishlistButton
│   ├── Cart/                 CartDrawer, CartCounter
│   └── Checkout/             CheckoutWizard
├── Models/                   User, Customer, Role, Permission, Category,
│                             Product, ProductImage, ProductVariant,
│                             Attribute, AttributeValue, Inventory,
│                             Cart, CartItem, Order, OrderItem, Address,
│                             Coupon, Banner, Review, ShippingZone, Setting
├── Policies/                 ProductPolicy, OrderPolicy, AddressPolicy …
├── Providers/                AppServiceProvider, AuthServiceProvider,
│                             EventServiceProvider, RepositoryServiceProvider
├── Repositories/
│   ├── Contracts/            ProductRepositoryInterface, OrderRepositoryInterface …
│   └── Eloquent/             ProductRepository, OrderRepository, CategoryRepository
├── Services/
│   ├── Cart/                 CartService, CartCalculator
│   ├── Catalog/              ProductFilterService, StockService
│   ├── Order/                OrderService, OrderNumberGenerator
│   ├── Payment/              PaymentGateway (interface), CashOnDeliveryGateway,
│   │                         WompiGateway 🔧
│   ├── Whatsapp/             WhatsappOrderService
│   └── Shipping/             ShippingCalculator
└── Support/                  helpers, traits (Money, Sluggable)

app/Filament/                 Admin (Resources, Pages, Widgets) — panel
config/                       rodsant.php (whatsapp, moneda, etc.)
database/                     migrations · factories · seeders
resources/
├── css/app.css               Tailwind + tokens de marca
├── js/app.js                 Alpine
└── views/                    layouts · components (Blade) · livewire · shop · account · checkout
routes/                       web.php · shop.php · console.php
tests/                        Feature · Unit
```

**Patrones aplicados:** Service Layer · Repository (consultas complejas) · Strategy (PaymentGateway) ·
Observer (Events/Listeners) · Form Request (validación) · Policy (autorización).

---

# 3. Diseño de Base de Datos

> Convenciones: `id` = `bigint unsigned AI PK`; FKs `*_id`; `timestamps`; `softDeletes` donde aplica.
> Motor **InnoDB**, charset `utf8mb4`.

### `users` (autenticación: staff y clientes registrados)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | varchar(150) | |
| email | varchar(180) | **unique**, index |
| phone | varchar(30) | nullable |
| password | varchar(255) | |
| email_verified_at | timestamp | nullable |
| remember_token, timestamps | | |

**Índices:** unique(email). **Relaciones:** N:M `roles`; 1:1 `customers`; 1:N `orders`.

### `roles` / `permissions` / pivotes (spatie)
- **`roles`**: id · name(unique) · guard_name · timestamps.
- **`permissions`**: id · name(unique) · guard_name · timestamps.
- **`model_has_roles`**, **`model_has_permissions`**, **`role_has_permissions`** (pivotes).
- **Relaciones:** roles N:M users; permissions N:M roles.

### `customers` (perfil de comprador; incluye invitados)
| id PK · user_id FK(nullable, unique) · full_name · email(index) · phone · accepts_marketing(bool) · notes · timestamps |

**Índices:** index(email), index(phone). **Relación:** belongsTo user (nullable → invitado); 1:N orders, addresses.

### `categories` (jerárquica)
| id PK · parent_id FK→categories(nullable) · name · slug(unique) · image · description · position(int) · gender(enum: unisex/hombre/mujer 🔧) · is_active(bool) · timestamps · softDeletes |

**Índices:** unique(slug), index(parent_id), index(is_active). **Relaciones:** self parent/children; 1:N products.

### `products`
| id PK · category_id FK · name · slug(unique) · sku(unique,nullable) · short_description · description(text) · base_price decimal(12,2) · sale_price decimal(12,2,nullable) · is_active(bool) · is_featured(bool) · is_new(bool) · views(int) · meta_title · meta_description · position · timestamps · softDeletes |

**Índices:** unique(slug), index(category_id), index(is_active,is_featured), index(base_price).
**Relaciones:** belongsTo category; 1:N images, variants; N:M attributes (vía values), collections; 1:N reviews.

### `product_images`
| id PK · product_id FK · variant_id FK(nullable) · path · alt · position(int) · is_primary(bool) · timestamps |

**Índices:** index(product_id), index(variant_id). **Relación:** belongsTo product/variant.

### `attributes` / `attribute_values`
- **`attributes`**: id PK · name(Talla/Color) · slug · type(enum: select,color) · position.
- **`attribute_values`**: id PK · attribute_id FK · value · meta(json, ej. hex) · position.
- **`attribute_product_variant`** (pivote variante↔valor): variant_id FK · attribute_value_id FK.

**Índices:** index(attribute_id); unique(variant_id, attribute_value_id) en pivote.

### `product_variants`
| id PK · product_id FK · sku(unique) · price_override decimal(12,2,nullable) · barcode(nullable) · is_active(bool) · timestamps |

**Índices:** unique(sku), index(product_id). **Relaciones:** belongsTo product; N:M attribute_values; 1:1 inventory; 1:N cart_items, order_items.

### `inventories` (stock separado → permite multi-bodega futuro)
| id PK · product_variant_id FK(unique) · quantity(int) · reserved(int, default 0) · low_stock_threshold(int) · timestamps |

**Índices:** unique(product_variant_id). **Relación:** belongsTo variant. *Disponible = quantity − reserved.*

### `carts`
| id PK · customer_id FK(nullable) · session_id(varchar, index, nullable) · coupon_id FK(nullable) · timestamps |

**Índices:** index(session_id), index(customer_id). **Relaciones:** 1:N cart_items; belongsTo coupon, customer.

### `cart_items`
| id PK · cart_id FK · product_variant_id FK · quantity(int) · unit_price decimal(12,2) · timestamps |

**Índices:** index(cart_id); unique(cart_id, product_variant_id). **Relación:** belongsTo cart, variant.

### `orders`
| id PK · order_number(varchar, unique) · customer_id FK(nullable) · user_id FK(nullable) · status(enum) · payment_status(enum) · payment_method(enum) · subtotal · discount_total · shipping_total · grand_total (decimal 12,2) · coupon_id FK(nullable) · shipping_address_id FK(nullable) · customer_name · customer_phone · customer_email · notes(text) · whatsapp_sent_at(timestamp,nullable) · timestamps · softDeletes |

**Índices:** unique(order_number), index(status), index(customer_id), index(created_at).
**Relaciones:** 1:N order_items; belongsTo customer, coupon, address.

### `order_items` (snapshot inmutable)
| id PK · order_id FK · product_variant_id FK(nullable) · product_name · variant_label · unit_price decimal(12,2) · quantity(int) · line_total decimal(12,2) · timestamps |

**Índices:** index(order_id). **Relación:** belongsTo order, variant.

### `addresses`
| id PK · customer_id FK(nullable) · full_name · phone · department · city · address_line · references · is_default(bool) · timestamps |

**Índices:** index(customer_id). **Relación:** belongsTo customer.

### `banners`
| id PK · type(enum: announcement,hero,collection,category,popup,strip) · title · subtitle · image_desktop · image_mobile · link_url · cta_label · position(int) · is_active(bool) · starts_at · ends_at(timestamp,nullable) · timestamps |

**Índices:** index(type,is_active), index(starts_at,ends_at). *Sin relación obligatoria; opcional FK a category/collection.*

### `settings` (clave/valor global)
| id PK · key(varchar, unique) · value(json) · group(varchar) · timestamps |

**Índices:** unique(key). *Ej: whatsapp_number, currency, social_links, free_shipping_from.*

### Tablas de apoyo
- **`coupons`**: id · code(unique) · type(percent/fixed) · value · min_subtotal · usage_limit · used_count · starts_at · expires_at · is_active.
- **`shipping_zones`** 🔧: id · name · department · city · cost · free_from(nullable) · estimated_days.
- **`reviews`**: id · product_id FK · customer_id FK · rating(tinyint 1-5) · title · body · is_approved(bool) · timestamps.
- **`collections`** + **`collection_product`** (drops): id · name · slug · cover_image · launches_at · is_active.

---

# 4. Relaciones Eloquent

```
User    1───1  Customer
User    N───N  Role  N───N  Permission
Customer 1──N  Order,  1──N  Address,  1──1  Cart

Category 1──N  Product           Category self (parent/children)
Product  1──N  ProductImage
Product  1──N  ProductVariant
Product  N──N  AttributeValue (a través de variantes)
Product  N──N  Collection
Product  1──N  Review

Attribute 1──N AttributeValue
ProductVariant N──N AttributeValue   (define la combinación talla/color)
ProductVariant 1──1 Inventory
ProductVariant 1──N CartItem
ProductVariant 1──N OrderItem

Cart  1──N CartItem        Cart  N──1 Coupon
Order 1──N OrderItem       Order N──1 Coupon,  N──1 Address,  N──1 Customer
```

**Definición por modelo:**

- `User`: `belongsToMany(Role)`, `hasOne(Customer)`.
- `Customer`: `belongsTo(User)`, `hasMany(Order)`, `hasMany(Address)`, `hasOne(Cart)`.
- `Category`: `belongsTo(Category,'parent_id')`, `hasMany(Category,'parent_id')`, `hasMany(Product)`.
- `Product`: `belongsTo(Category)`, `hasMany(ProductImage)`, `hasMany(ProductVariant)`, `belongsToMany(Collection)`, `hasMany(Review)`.
- `ProductVariant`: `belongsTo(Product)`, `belongsToMany(AttributeValue)`, `hasOne(Inventory)`.
- `Attribute`: `hasMany(AttributeValue)`.
- `Cart`: `hasMany(CartItem)`, `belongsTo(Coupon)`, `belongsTo(Customer)`.
- `Order`: `hasMany(OrderItem)`, `belongsTo(Customer)`, `belongsTo(Coupon)`, `belongsTo(Address)`.
- `Banner`, `Setting`, `Coupon`, `ShippingZone`: independientes / configuración.

---

# 5. Panel Administrativo (Filament)

| Módulo | Funcionalidades |
|---|---|
| **Dashboard** | KPIs: ventas día/mes, pedidos por estado, ticket promedio, productos con bajo stock, top productos, último drop. Widgets de gráficas y accesos rápidos. |
| **Productos** | CRUD completo; subida múltiple de imágenes (drag&drop, reordenar, foto principal); precio y precio promocional; tallas y colores; **inventario por variante**; estado Disponible/Agotado (auto); destacado; etiqueta NUEVO; SEO (meta); vista previa; papelera/restaurar. |
| **Categorías** | CRUD jerárquico, imagen, orden (arrastrar), género 🔧, activar/desactivar, conteo de productos. |
| **Inventario** | Vista global de stock por variante, edición rápida en lote, alertas de bajo stock, filtro por agotados. |
| **Pedidos** | Tablero por estado, detalle con items y totales, cambio de estado (pendiente→confirmado→pagado→enviado→entregado), reenviar WhatsApp/email, notas internas, exportar. |
| **Clientes** | Listado, historial de pedidos, contacto, direcciones, marketing opt-in. |
| **Banners** | Gestor visual de la home: tipo, imagen escritorio/móvil, título, CTA, enlace, vigencia (programar drops), orden, activar/desactivar. |
| **Configuración** | Número WhatsApp, moneda, envío gratis desde $X, redes sociales, datos de tienda, páginas legales, integraciones (GA4/Pixel). |
| **Usuarios** | Gestión de staff, asignación de roles/permisos (solo Super Admin). |

Todo en **español**, lenguaje no técnico, validaciones amables, confirmaciones y publicación automática en la tienda al guardar.

---

# 6. Frontend

| Página | Contenido / función |
|---|---|
| **Home** | Announcement bar · hero/drop · categorías rápidas · novedades · colección/lookbook · bestsellers · bloque de marca · trust · Instagram · newsletter · footer · WhatsApp flotante. |
| **Catálogo** | Grid responsive (2/3/4/5 col), **filtros AJAX** (categoría, talla, color, precio, disponibilidad, género/drop), 8 órdenes, chips de filtros activos, scroll infinito (móvil)/paginación. |
| **Categorías** | Cabecera editorial + listado filtrable de la categoría; navegación por subcategorías. |
| **Producto (PDP)** | Galería editorial con zoom, selector color (swatches) + talla (con stock), guía de tallas (modal), cantidad, añadir al carrito, comprar por WhatsApp, acordeones (descripción, materiales, envíos, cambios), badges, reseñas, "completa el look", relacionados, barra de compra sticky en móvil. |
| **Carrito** | Drawer lateral: editar cantidades en vivo, eliminar, cupón, subtotales, upsell envío gratis, CTA a checkout WhatsApp. |
| **Checkout WhatsApp** | Una pantalla (invitado): datos de contacto/envío, cálculo de envío, notas, método; genera pedido + mensaje. |
| **Contacto** | Formulario, WhatsApp, ubicación física (mapa), horarios, redes. |

---

# 7. Flujo de Compra

```
1. CLIENTE entra a la tienda (cualquier dispositivo)
        ▼
2. Navega catálogo → filtra/busca → abre PRODUCTO
        ▼
3. En el PDP elige COLOR + TALLA (valida stock en vivo) → "Añadir al carrito"
        ▼
4. Se abre el CARRITO (drawer): ajusta cantidades, aplica cupón
   → ve subtotal, descuento, "te falta $X para envío gratis"
        ▼
5. "FINALIZAR POR WHATSAPP"
        ▼
6. CHECKOUT (invitado): nombre, teléfono, ciudad, dirección, notas
   → calcula envío según zona
        ▼
7. Sistema crea PEDIDO real (RS-2026-000123, estado: pendiente)
   • snapshot de items y totales
   • reserva stock (inventories.reserved)
        ▼
8. Genera MENSAJE WhatsApp formateado y abre wa.me/57XXXX?text=...
   • marca whatsapp_sent_at
   • Job async: email al cliente + notificación al admin
        ▼
9. CONFIRMACIÓN en pantalla ("Te contactaremos por WhatsApp")
        ▼
10. Asesor gestiona desde el panel:
    pendiente → confirmado → pagado → enviado → entregado
    (cada cambio notifica al cliente; al confirmar pago se descuenta stock real)
```

---

# 8. Roles y Permisos

| Rol | Alcance |
|---|---|
| **Super Admin** | Control total: configuración crítica, **gestión de usuarios y roles**, integraciones, todos los módulos. (Desarrollador / dueña principal.) |
| **Administrador** | Operación diaria: productos, categorías, inventario, pedidos, clientes, banners, cupones, reseñas. **No** gestiona usuarios/roles ni configuración crítica. (Propietaria y su equipo.) |
| **Cliente** | Tienda: navegar, comprar, ver sus pedidos, wishlist, direcciones, reseñas. Sin acceso al panel. |

**Matriz (resumen):** `settings.manage` y `users.manage` → solo Super Admin.
`products/categories/orders/banners/inventory.manage` → Super Admin + Administrador.
`place_order/wishlist/own_orders` → Cliente.
Aplicado con **spatie/permission** + **Policies** (el cliente solo accede a *sus* datos).

---

# 9. Estrategia Responsive (Mobile First)

| Rango | Ancho | Catálogo | Comportamiento |
|---|---|---|---|
| **Móvil** | 360–639 | 2 col | Barra inferior fija (Inicio/Buscar/Carrito/Cuenta/WhatsApp), drawers full-screen, compra sticky en PDP, menú hamburguesa, scroll infinito. |
| **Tablet** | 640–1023 | 3 col | Filtros en panel colapsable, galería 2-up, menú híbrido. |
| **Desktop** | 1024–1535 | 3–4 col | Mega-menú, filtros sidebar fijo, hover states, lookbook a sangre. |
| **Alta resolución** | ≥1536 | 4–5 col | Contenedor máx ~1600px centrado, imágenes 2x/retina, tipografía fluida `clamp()`. |

**Transversal:** targets táctiles ≥44px, gestos swipe, imágenes WebP/AVIF + `srcset`, lazy-load,
*skeletons*. Objetivo Core Web Vitals "buenos" (LCP<2.5s, CLS<0.1, INP<200ms) y Lighthouse ≥90.
Accesibilidad **WCAG 2.1 AA** y SEO técnico (URLs limpias, Schema.org Product/Offer, sitemap, OG).

---

# 10. Roadmap de Desarrollo

| Fase | Objetivo | Incluye | Resultado |
|---|---|---|---|
| **Fase 1 — Fundación + Panel** | Base técnica y administración | Setup Laravel 12/PHP 8.4, Tailwind/Vite, Livewire, Alpine, MySQL; estructura de carpetas; migraciones y modelos completos; roles/permisos; **Filament**: productos+variantes+inventario+categorías+imágenes+banners+usuarios. Seeders. | La dueña ya puede **cargar y gestionar** todo el catálogo. |
| **Fase 2 — Tienda pública** | Frontend de navegación | Home con banners dinámicos, catálogo con filtros/orden AJAX, PDP con variantes y guía de tallas, búsqueda, páginas estáticas, **responsive completo** (5 rangos), SEO técnico, optimización de imágenes. | Catálogo público navegable y rápido (sin compra aún). |
| **Fase 3 — Carrito + Pedidos WhatsApp** | Conversión (MVP vendible) | CartService + drawer, cupones, cálculo de envío, checkout invitado, **flujo de pedido por WhatsApp**, emails y notificaciones, panel de pedidos con estados. | **Tienda lista para vender** (cierre por WhatsApp). |
| **Fase 4 — Cuenta, fidelización y growth** | Profesionalización | Registro/login, dashboard cliente, historial/seguimiento, direcciones, **wishlist**, **reseñas**, carritos abandonados, Meilisearch, GA4/Pixel, hardening de seguridad, tests, **despliegue a producción**. *(Pasarela en línea Wompi/PayU 🔧 se integra aquí o como Fase 5.)* | Tienda completa, medible y **en producción**. |

> **Hito comercial:** al final de **Fase 3** RodSant ya vende. Fase 4 añade profesionalismo sin bloquear la operación.

---

## Pendiente de aprobación (4 confirmaciones 🔧)

1. **Catálogo unisex con filtro de género** — ¿correcto, o separar Hombre/Mujer como secciones?
2. **Pago en lanzamiento: WhatsApp + contraentrega/transferencia** (pasarela en Fase 4) — ¿de acuerdo?
3. **Filament** como base del panel — ¿aprobado?
4. **Roles Super Admin / Administrador / Cliente** tal como se definieron — ¿correcto?
