FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

FROM serversideup/php:8.4-fpm-nginx

ENV APP_ENV=production \
    APP_DEBUG=false \
    AUTORUN_ENABLED=true \
    HEALTHCHECK_PATH=/up \
    PHP_OPCACHE_ENABLE=1

COPY --from=vendor --chown=www-data:www-data /app /var/www/html

EXPOSE 8080
