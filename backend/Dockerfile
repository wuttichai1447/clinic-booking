FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    git unzip libzip-dev postgresql-dev sqlite-dev icu-dev icu-data-full oniguruma-dev \
    && docker-php-ext-install \
        pdo_pgsql pdo_sqlite zip mbstring bcmath pcntl intl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

ENV PORT=8000
EXPOSE 8000

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
