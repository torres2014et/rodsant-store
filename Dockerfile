# syntax=docker/dockerfile:1

############################################################
# Etapa 1 — Compilar los assets del front-end con Vite
############################################################
FROM node:20-alpine AS assets

WORKDIR /app

# Instala dependencias usando el lockfile (build reproducible).
COPY package.json package-lock.json ./
RUN npm ci

# Copia el resto del código y compila CSS/JS a public/build.
COPY . .
RUN npm run build


############################################################
# Etapa 2 — Runtime PHP de producción (nginx + php-fpm)
# Base: serversideup/php (trae zip, gd, pdo_mysql, opcache, etc.)
# OJO: NO trae ext-intl por defecto → se instala abajo (Filament lo exige).
############################################################
FROM serversideup/php:8.2-fpm-nginx AS app

# --- Extensiones PHP adicionales -------------------------------------------
# Filament 4 (filament/support, forms, tables, panels, query-builder) requiere
# ext-intl. La imagen base NO la incluye. Usamos install-php-extensions (viene
# embebido en serversideup/php), que compila intl e instala las librerías ICU
# necesarias automáticamente. Requiere usuario root; al terminar volvemos a
# www-data (usuario no privilegiado con el que corre la app).
USER root
RUN install-php-extensions intl
USER www-data

# En cada arranque: corre migraciones y crea el enlace de storage.
# (La carga de datos demo se hace UNA vez, a mano — ver DEPLOY-RAILWAY.md.)
ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/html

# Composer desde su imagen oficial.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código de la aplicación (propietario del usuario web del contenedor).
COPY --chown=www-data:www-data . .

# Assets ya compilados en la etapa 1.
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Dependencias PHP de producción (sin paquetes de desarrollo).
# Ya con ext-intl presente, Composer valida los requisitos de plataforma sin
# necesidad de --ignore-platform-req.
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# La imagen base ya expone el puerto 8080 y arranca nginx + php-fpm.
