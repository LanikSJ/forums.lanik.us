# Stage 1: Install Composer Dependencies
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json ./
# Install dependencies without dev-dependencies or platform requirements checks
RUN composer install --no-dev --ignore-platform-reqs --no-interaction --no-scripts

# Stage 2: PHP-FPM Server
FROM php:8.0-fpm-alpine

# Install PHP extensions required by phpBB on Alpine
RUN apk add --no-cache \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        libxml2-dev \
        icu-dev \
        shadow \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        zip \
        opcache \
        intl \
        bcmath

# Copy project files
COPY . /var/www/html/

# Copy dependencies from Composer Stage
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Set correct ownership & permissions for phpBB writable folders
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/cache /var/www/html/files /var/www/html/store /var/www/html/images/avatars/upload

WORKDIR /var/www/html

EXPOSE 9000
