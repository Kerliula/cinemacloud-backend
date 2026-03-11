# ================================
# Stage 1 - Builder (thrown away)
# ================================
ARG PHP_VERSION=${PHP_VERSION}
FROM php:${PHP_VERSION}-fpm-alpine AS builder

# Install build-time tools only
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Leverage cache for dependencies
COPY src/composer.json src/composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy app code and generate optimized autoloader
COPY src/ ./
RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump --no-dev 2>/dev/null || true

# ================================
# Stage 2 - Final clean image
# ================================
FROM php:${PHP_VERSION}-fpm-alpine AS base

ARG INSTALL_XDEBUG=false

# Only install PHP extensions needed at runtime
# Note: opcache is built-in and enabled by default in PHP 8.5+
RUN apk add --no-cache $PHPIZE_DEPS \
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


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy only the built app from stage 1 — no build tools come with it
COPY --from=builder /var/www/html ./

# Permissions
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Opcache configuration for production (use a separate file for development overrides)
COPY docker/app/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Security configuration to hide PHP and server information
COPY docker/app/security.ini /usr/local/etc/php/conf.d/security.ini

EXPOSE 9000
USER www-data
CMD ["php-fpm"]