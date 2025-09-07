#!/usr/bin/env bash
set -e

# Ins Arbeitsverzeichnis
cd /var/www/html

# Composer-Install nur wenn composer.json existiert
if [ -f composer.json ]; then
  echo ">> Running composer install"
  composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
fi

# Rechte für Laravel-typische Verzeichnisse (safe no-ops wenn nicht vorhanden)
if [ -d storage ] && [ -d bootstrap/cache ]; then
  echo ">> Fixing permissions for storage/ and bootstrap/cache/"
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R ug+rwx storage bootstrap/cache || true
fi

# Auf Datenbank warten (nur wenn DB_HOST gesetzt ist)
if [ -n "${DB_HOST}" ]; then
  DB_PORT="${DB_PORT:-3306}"
  echo ">> Waiting for database at ${DB_HOST}:${DB_PORT}"
  for i in {1..60}; do
    if nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1; then
      echo ">> Database is up."
      break
    fi
    if [ "$i" -eq 60 ]; then
      echo "!! Database did not become ready in time." >&2
      exit 1
    fi
    sleep 1
  done
fi

# Laravel-spezifisch: Key generieren (idempotent) & Migrationen ausführen
if [ -f artisan ]; then
  echo ">> Running artisan tasks"
  php artisan key:generate --force || true
  php artisan config:cache || true

  echo ">> Running migrations"
  php artisan migrate --force --no-interaction || {
    echo "!! Migrations failed" >&2
    exit 1
  }
fi

# Zum Schluss den Apache im Vordergrund starten
exec apache2-foreground
