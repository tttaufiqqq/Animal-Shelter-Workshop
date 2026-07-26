# syntax=docker/dockerfile:1

# ---- Stage 1: PHP dependencies (Composer) ----
# Split from the runtime stage so `vendor/` is cached separately from the
# app's own source — composer.json/lock rarely change compared to app code.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ---- Stage 2: frontend assets (Vite) ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public/ public/
RUN npm run build

# ---- Stage 3: runtime (php-fpm) ----
# Alpine + explicit extension list, mirroring
# infrastructure/ansible/playbooks/app-server.yml's "Install PHP 8.3 and
# required extensions" task exactly (mbstring, xml, curl, zip, bcmath, pdo,
# pdo_mysql, pdo_pgsql, gd, intl, redis) — xml/pdo/curl's dependencies ship
# built-in on the official php image, the rest need explicit install.
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        icu-libs \
        libpq \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        postgresql-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        curl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        curl \
        zip \
        bcmath \
        pdo_mysql \
        pdo_pgsql \
        gd \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# bootstrap/cache/*.php is .dockerignore'd — it's excluded so this step
# regenerates it against the --no-dev vendor/ actually shipped in this image.
# A host-generated copy (built with dev deps like laravel/pail) baked in
# via COPY . . above caused a real "PailServiceProvider not found" 500 the
# first time this image was built and run.
RUN php artisan package:discover --no-interaction

# Framework tables (cache/sessions/migrations ledger) live on this file —
# the app's own 5 named connections (reporting/shelter/animals/booking/users)
# are configured entirely via env vars at runtime, not baked into the image.
RUN touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database/database.sqlite \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
