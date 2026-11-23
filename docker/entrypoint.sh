#!/bin/sh
# Démarrage du conteneur GameHub : port, base de démo, puis Apache.
set -e
cd /var/www/html

PORT="${PORT:-8080}"

# Apache écoute sur le port imposé par l'hébergeur (Render : PORT).
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ "${DB_DRIVER:-sqlite}" = "sqlite" ]; then
  DB_PATH="${DB_PATH:-/var/www/html/var/gamehub.sqlite}"
  if [ ! -f "$DB_PATH" ]; then
    echo "Création de la base SQLite de démonstration…"
    mkdir -p "$(dirname "$DB_PATH")"
    php bin/migrate.php
    php bin/seed.php
  fi
  chown -R www-data:www-data "$(dirname "$DB_PATH")"
else
  echo "Base MySQL : application des migrations…"
  php bin/migrate.php
  php bin/seed.php --if-empty
fi

exec apache2-foreground
