# syntax=docker/dockerfile:1
FROM node:24-bookworm-slim AS node

FROM php:8.4-apache-bookworm AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libpq-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" intl pcntl pdo_mysql pdo_pgsql zip opcache \
    && a2enmod rewrite \
    && printf 'Listen 8080\n' > /etc/apache2/ports.conf \
    && printf 'ServerName localhost\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/server-name.conf \
    && a2enconf server-name \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY docker-apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker-php.ini /usr/local/etc/php/conf.d/app.ini
COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/app-entrypoint

RUN mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080
STOPSIGNAL SIGTERM
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]

FROM runtime AS development
ARG LOCAL_UID=1000
ARG LOCAL_GID=1000
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN groupmod -o -g "$LOCAL_GID" www-data \
    && usermod -o -u "$LOCAL_UID" www-data \
    && ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && mkdir -p vendor node_modules \
    && chown -R www-data:www-data /var/www /var/run/apache2 /var/lock/apache2
ENV APP_ENV=local
USER www-data

FROM runtime AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && composer check-platform-reqs --no-dev

FROM node AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts
COPY . .
COPY --from=dependencies /var/www/html/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
RUN npm run build

FROM runtime AS production
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
COPY --from=dependencies /var/www/html /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
RUN mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage
USER www-data
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1:8080/up > /dev/null || exit 1
