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
  # Seed only a database with no users, so restarts keep passwords and edits.
  # DatabaseSeeder runs in one transaction, so an interrupted seed leaves no users and retries.
  user_count="$(php artisan tinker --execute='echo App\Models\User::count();' 2>&1 | tail -n 1)"
  case "$user_count" in
    0) php artisan db:seed --force || true ;;
    ''|*[!0-9]*) echo "Skipping seed, could not read the user count: $user_count" ;;
  esac
  php artisan translations:sync || true
fi

exec "$@"
