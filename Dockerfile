FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    libsodium-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        mbstring \
        opcache \
        pdo \
        pdo_pgsql \
        sodium \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

ENV APP_ENV=production
ENV APP_DEBUG=false

COPY . .

RUN composer install \
    --optimize-autoloader \
    --no-interaction \
    --no-dev

RUN php artisan l5-swagger:generate

COPY start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["sh", "/usr/local/bin/start.sh"]