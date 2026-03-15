# ================================
# Stage 1 - Builder (thrown away)
# ================================
ARG PHP_VERSION=${PHP_VERSION}
FROM php:${PHP_VERSION}-fpm-alpine AS builder

RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY src/composer.json src/composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY src/ ./
RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump --no-dev 2>/dev/null || true

# ================================
# Stage 2 - Final clean image
# ================================
FROM php:${PHP_VERSION}-fpm-alpine AS base

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apk add --no-cache $PHPIZE_DEPS shadow \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then \
        apk add --no-cache linux-headers \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug; \
    fi \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/* /tmp/pear

# Remap www-data UID/GID to match host user
RUN usermod -u ${WWWUSER} www-data \
    && groupmod -g ${WWWGROUP} www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=builder /var/www/html ./

RUN mkdir -p \
        storage/logs \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwX,o-rwx storage bootstrap/cache

COPY --chown=www-data:www-data docker/app/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY --chown=www-data:www-data docker/app/security.ini /usr/local/etc/php/conf.d/security.ini

EXPOSE 9000
USER www-data
CMD ["php-fpm"]