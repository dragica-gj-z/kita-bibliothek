#!/usr/bin/env bash
set -e

cd /var/www/html

# .env auto kopieren, falls noch nicht vorhanden
if [ ! -f .env ] && [ -f .env.example ]; then
  echo ">> No .env found — copying from .env.example"
  cp .env.example .env
fi

# Composer install
if [ -f composer.json ]; then
  echo ">> composer install"
  composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
fi

# Fix Rechte (idempotent)
if [ -d storage ] && [ -d bootstrap/cache ]; then
  echo ">> Fixing permissions"
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R ug+rwx storage bootstrap/cache || true
fi

# Auf DB warten, wenn Variablen gesetzt
if [ -n "${DB_HOST}" ]; then
  DB_PORT="${DB_PORT:-3306}"
  echo ">> Waiting for database at ${DB_HOST}:${DB_PORT}"
  for i in {1..60}; do
    if nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1; then
      echo ">> Database is up."
      break
    fi
    [ "$i" -eq 60 ] && { echo "!! DB not ready"; exit 1; }
    sleep 1
  done
fi

# Laravel-Tasks
if [ -f artisan ]; then
  echo ">> Laravel bootstrap"
  php artisan key:generate --force || true
  php artisan config:clear || true
  php artisan cache:clear || true
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
  php artisan storage:link || true

  echo ">> Running migrations"
  php artisan migrate --force --no-interaction

  # Optional: Seeder automatisch
  if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    echo ">> Seeding database"
    php artisan db:seed --force || true
  fi
fi

exec apache2-foreground
