# GameHub v2 — image de démonstration (PHP 8.3 + Apache + SQLite/MySQL)
# La base SQLite est créée et remplie au premier démarrage du conteneur.
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev \
 && docker-php-ext-install pdo_sqlite pdo_mysql \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/gamehub.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/gamehub-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/gamehub-entrypoint && chmod +x /usr/local/bin/gamehub-entrypoint \
 && printf 'ServerName localhost\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/gamehub.conf \
 && a2enconf gamehub

WORKDIR /var/www/html
COPY . .
RUN mkdir -p var && chown -R www-data:www-data var

# Render (offre gratuite) impose le port via la variable PORT.
ENV PORT=8080 \
    APP_ENV=production \
    APP_DEMO=1 \
    DB_DRIVER=sqlite \
    DB_PATH=/var/www/html/var/gamehub.sqlite
EXPOSE 8080
CMD ["gamehub-entrypoint"]
