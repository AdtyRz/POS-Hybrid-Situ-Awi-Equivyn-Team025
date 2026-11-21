# syntax=docker/dockerfile:1

# =====================================================================
# Image mandiri (satu container) untuk Tugas 1 & deployment Coolify.
# Apache + PHP 8.4, document root langsung ke folder public Laravel.
# Database & Redis tetap service terpisah (disediakan Coolify).
# =====================================================================

# ---------- Stage 1: build aset frontend ----------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .
RUN npm run build

# ---------- Stage 2: runtime Apache + PHP ----------
FROM php:8.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PORT=80

RUN apt-get update && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        curl \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && printf '\n\n\n\n' | pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && printf '%s\n' \
        'ServerName localhost' \
        'ServerTokens Prod' \
        'ServerSignature Off' \
        '<IfModule mod_headers.c>' \
        '    Header always set X-Content-Type-Options "nosniff"' \
        '    Header always set X-Frame-Options "SAMEORIGIN"' \
        '    Header always set Referrer-Policy "strict-origin-when-cross-origin"' \
        '    Header always set Permissions-Policy "camera=(self), microphone=(), geolocation=()"' \
        '    Header always set Content-Security-Policy "default-src '\''self'\''; script-src '\''self'\'' '\''unsafe-inline'\'' '\''unsafe-eval'\'' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src '\''self'\'' '\''unsafe-inline'\'' https://cdn.tailwindcss.com; img-src '\''self'\'' data: blob: https:; font-src '\''self'\'' data: https:; connect-src '\''self'\'' https: ws: wss:; media-src '\''self'\'' blob:; object-src '\''none'\''; base-uri '\''self'\''; form-action '\''self'\''; frame-ancestors '\''self'\'';"' \
        '</IfModule>' \
        > /etc/apache2/conf-available/zz-situawi.conf \
    && a2enconf zz-situawi \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-situawi.ini
COPY docker/php/entrypoint-deploy.sh /usr/local/bin/entrypoint-deploy.sh
RUN chmod +x /usr/local/bin/entrypoint-deploy.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint-deploy.sh"]
CMD ["apache2-foreground"]
