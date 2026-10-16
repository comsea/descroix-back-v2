FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

FROM php:8.2-apache

COPY public/ /var/www/html/
COPY bootstrap.php /var/www/bootstrap.php
COPY src/ /var/www/src/
COPY cron/ /var/www/cron/
COPY --from=vendor /app/vendor /var/www/vendor

RUN chown -R www-data:www-data /var/www \
  && chmod 755 /var/www/cron/sync.php

RUN mkdir -p /var/www/data \
  && chown www-data:www-data /var/www/data \
  && chmod 755 /var/www/data

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
