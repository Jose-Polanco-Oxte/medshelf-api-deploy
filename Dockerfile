# ============================================================
# Stage 1: Composer dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative

# ============================================================
# Stage 2: Final image con FrankenPHP
# ============================================================
FROM dunglas/frankenphp:php8.4-alpine

LABEL maintainer="tu-email@ejemplo.com"

# ── Extensiones del sistema ──────────────────────────────────
RUN apk add --no-cache \
    libpng-dev \
    postgresql-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    git \
    supervisor \
    nodejs \
    npm \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# ── Configuración PHP para producción ───────────────────────
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# ── Directorio de trabajo ────────────────────────────────────
WORKDIR /app

# ── Código de la aplicación ──────────────────────────────────
COPY --from=vendor /app/vendor ./vendor
COPY . .

# ── Permisos de Laravel ──────────────────────────────────────
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Caddyfile para Laravel ───────────────────────────────────
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile

# ── Supervisor (web + queue worker) ─────────────────────────
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ── Script de entrada ────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
