FROM composer:latest AS vendor
WORKDIR /app
COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --optimize-autoloader

COPY . .

FROM ubuntu:latest AS ext-script
RUN echo "apt-get update && apt-get install -y libpq-dev unzip curl && docker-php-ext-install pdo pdo_pgsql pgsql pcntl opcache && pecl install redis && docker-php-ext-enable redis opcache" > /install-ext.sh

FROM php:8.4-fpm AS fpm

COPY --from=ext-script /install-ext.sh /install-ext.sh
RUN sh /install-ext.sh && rm /install-ext.sh

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm"]

FROM php:8.4-cli AS cli

COPY --from=ext-script /install-ext.sh /install-ext.sh
RUN sh /install-ext.sh && rm /install-ext.sh

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php", "artisan", "queue:work", "--sleep=3", "--tries=3"]