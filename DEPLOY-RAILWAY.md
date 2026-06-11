# 🚂 Desplegar RodSant Store en Railway (gratis)

Guía paso a paso para publicar la tienda en una URL de Railway (`https://algo.up.railway.app`).
El proyecto ya viene **listo** (Dockerfile, migraciones automáticas, etc.). Solo sigue estos pasos.

> ⚠️ El plan gratuito de Railway es **crédito de prueba (~$5)**. Alcanza para mostrar la demo un buen rato; si se agota, la app se pausa hasta que recargues o agregues un método de pago.

---

## Antes de empezar
- Una cuenta de **GitHub** (gratis).
- Una cuenta de **Railway** → https://railway.app (entra con GitHub).
- Tener **Git** instalado (ya lo tienes).

---

## Paso 1 — Subir el código a GitHub

Desde la carpeta del proyecto (`C:\xampp\htdocs\ROPA`):

```bash
git init
git add .
git commit -m "RodSant Store - listo para desplegar"
```

Crea un repositorio **privado** vacío en GitHub (sin README), copia su URL y:

```bash
git branch -M main
git remote add origin https://github.com/TU_USUARIO/rodsant.git
git push -u origin main
```

> Si te pide usuario/clave, GitHub usa un **token** en vez de contraseña, o instala **GitHub Desktop** y sube desde ahí (más fácil).

---

## Paso 2 — Crear el proyecto en Railway

1. En Railway → **New Project** → **Deploy from GitHub repo** → elige tu repo `rodsant`.
2. Railway detectará el **Dockerfile** y empezará a construir. Déjalo (fallará la 1ª vez porque aún no hay base de datos; lo arreglamos ya).

---

## Paso 3 — Agregar la base de datos MySQL

1. Dentro del proyecto → **New** → **Database** → **Add MySQL**.
2. Espera a que quede activa.

---

## Paso 4 — Configurar las variables de entorno

Entra al **servicio de la app** (no al de MySQL) → pestaña **Variables** → agrega estas:

```
APP_NAME=RodSant Store
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # ← ver abajo cómo generarla
APP_URL=https://TU-DOMINIO.up.railway.app   # ← lo ajustas en el Paso 6

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

LOG_CHANNEL=stderr

# Base de datos (referencias al servicio MySQL de Railway)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

# Sesión y caché en la base de datos (las tablas se crean con las migraciones)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Número de WhatsApp de la tienda (luego se puede cambiar desde el panel)
RODSANT_WHATSAPP_NUMBER=573000000000
```

### Generar el `APP_KEY`
En tu PC, en la carpeta del proyecto:

```bash
php artisan key:generate --show
```

Copia el valor que empieza con `base64:...` y pégalo en la variable `APP_KEY` de Railway.

> Las `${{MySQL.*}}` son **referencias** de Railway: escríbelas tal cual y Railway las reemplaza por los datos reales de tu MySQL. Si tu servicio de base de datos se llama distinto a `MySQL`, ajusta el nombre.

---

## Paso 5 — Volumen para las imágenes (persistencia)

Para que las imágenes de la tienda no se borren en cada actualización:

1. En el servicio de la app → **Settings** → **Volumes** → **New Volume**.
2. Mount path: `/var/www/html/storage/app/public`
3. Guarda. Railway hará un redeploy.

---

## Paso 6 — Dominio + datos demo

1. En el servicio de la app → **Settings** → **Networking** → **Generate Domain**.
   - Copia el dominio (`https://algo.up.railway.app`) y pégalo en la variable **`APP_URL`** (Paso 4). Guarda → redeploy.
2. Cuando el deploy quede en verde, **carga los datos demo una sola vez**:
   - Abre el servicio de la app → menú **⋮ / Command** (o la pestaña de terminal/CLI de Railway) y ejecuta:
     ```bash
     php artisan db:seed --force
     ```
   - Esto crea categorías, los 22 productos con imágenes, banners, pedidos y el usuario admin.

> Las **migraciones** corren solas en cada arranque; el **seed** se hace una vez (este paso). Si algún día quieres reiniciar la demo: `php artisan migrate:fresh --seed --force`.

---

## ✅ Listo

- Tienda: `https://algo.up.railway.app`
- Panel: `https://algo.up.railway.app/admin` → `admin@rodsantstore.com` / `password`

**Primero que todo en producción:** entra al panel → **Configuración** y cambia el **número de WhatsApp** por el real, y **cambia la contraseña** del usuario admin.

---

## 🆘 Si algo falla
- **El build falla:** abre los *Deploy Logs* en Railway y copia el error (me lo pasas y lo resolvemos).
- **Error 500 / pantalla blanca:** revisa que `APP_KEY` esté puesta y que las variables `DB_*` apunten al MySQL.
- **El panel se ve sin estilos:** confirma que el deploy terminó (los assets se compilan en el build) y que `APP_URL` es el dominio `https` correcto.
- **Imágenes rotas:** confirma el **volumen** del Paso 5 y que ya corriste el `db:seed`.

## Alternativa sin GitHub (Railway CLI)
Si no quieres usar GitHub:
```bash
npm i -g @railway/cli
railway login
railway init
railway up
```
Igual debes agregar MySQL, variables y volumen (Pasos 3–6) desde el panel de Railway.
