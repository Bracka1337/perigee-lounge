#!/usr/bin/env sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

if [ "${AUTO_SETUP:-0}" = "1" ]; then
  if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
  fi

  echo "Waiting for MySQL at db:3306..."
  for i in $(seq 1 60); do
    if mysqladmin ping -h"db" -P"3306" -u"${DB_USERNAME:-perigee}" -p"${DB_PASSWORD:-perigee_pass}" --silent; then
      break
    fi
    sleep 1
  done

  if [ -f .env ] && grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force || true
  fi
  php artisan storage:link || true
  php artisan migrate --force || true
  php artisan db:seed --force || true
fi

exec "$@"
