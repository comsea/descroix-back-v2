FROM php:8.2-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends cron \
  && rm -rf /var/lib/apt/lists/*

COPY api/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

COPY sync/index.php /opt/sync/index.php
RUN chmod 755 /opt/sync/index.php

COPY sync/crontab /etc/cron.d/descroix-sync
RUN chmod 0644 /etc/cron.d/descroix-sync

RUN mkdir -p /var/www/data \
  && chown www-data:www-data /var/www/data \
  && chmod 755 /var/www/data

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
