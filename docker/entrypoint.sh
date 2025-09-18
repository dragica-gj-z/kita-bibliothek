#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# .env vorbereiten, falls noch nicht vorhanden
if [ ! -f .env ]; then
  cp .env.example .env || true
fi

# Basis-Setup
php artisan key:generate --force || true
php artisan storage:link || true

# ENV-Defaults lesen
APP_ENV_VAL=${APP_ENV:-production}
BUILD_ASSETS_VAL=${BUILD_ASSETS:-true}
FORCE_ASSETS_BUILD_VAL=${FORCE_ASSETS_BUILD:-false}

# Dev-Optionen (kannst du per ENV übersteuern)
MIGRATE_ON_START=${MIGRATE_ON_START:-true}   # nur Dev: Migrationen automatisch
WAIT_FOR_DB_RETRIES=${WAIT_FOR_DB_RETRIES:-10}
WAIT_FOR_DB_SLEEP=${WAIT_FOR_DB_SLEEP:-3}

if [ "$APP_ENV_VAL" = "local" ]; then
  echo "[entrypoint] DEV-Modus erkannt (APP_ENV=local)"

  # Alle Caches weg, damit Änderungen/Fehler sofort sichtbar sind
  php artisan optimize:clear || true

  # Migrationen im Dev automatisch (mit kurzer Retry-Schleife, falls DB noch nicht bereit)
  if [ "$MIGRATE_ON_START" = "true" ]; then
    echo "[entrypoint] Führe Migrationen aus (mit bis zu $WAIT_FOR_DB_RETRIES Versuchen)"
    attempt=1
    until php artisan migrate --force; do
      if [ "$attempt" -ge "$WAIT_FOR_DB_RETRIES" ]; then
        echo "[entrypoint] Migrationen fehlgeschlagen nach $attempt Versuchen."
        exit 1
      fi
      echo "[entrypoint] DB/Migration noch nicht bereit – Versuch $attempt fehlgeschlagen. Warte ${WAIT_FOR_DB_SLEEP}s …"
      attempt=$((attempt+1))
      sleep "$WAIT_FOR_DB_SLEEP"
    done
  else
    echo "[entrypoint] Überspringe Migrationen (MIGRATE_ON_START=false)"
  fi

  # Kein Asset-Build im Dev – Vite-Dev-Server liefert die Assets
  echo "[entrypoint] Überspringe Asset-Build (Vite-Dev-Server übernimmt)"

else
  echo "[entrypoint] Nicht-DEV: wende Prod-Optimierungen an"

  # Prod-Caches einschalten
  php artisan config:cache  || true
  php artisan route:cache   || true
  php artisan view:cache    || true

  # Optional: Migrationen in Prod per ENV aktivierbar (standard: aus)
  if [ "${RUN_MIGRATIONS_ON_START:-false}" = "true" ]; then
    echo "[entrypoint] Prod: Führe Migrationen aus (RUN_MIGRATIONS_ON_START=true)"
    php artisan migrate --force
  else
    echo "[entrypoint] Prod: Überspringe Migrationen (RUN_MIGRATIONS_ON_START=false)"
  fi

  # Assets für Produktion bauen (falls nicht per ENV unterdrückt)
  if [ "$FORCE_ASSETS_BUILD_VAL" = "true" ] || [ "$BUILD_ASSETS_VAL" = "true" ]; then
    echo "[entrypoint] Baue Assets für Produktion"
    npm ci
    npm run build
  else
    echo "[entrypoint] Überspringe Asset-Build (via ENV)"
  fi
fi

# Rechte auf storage und bootstrap/cache setzen
echo "[entrypoint] Setze Dateirechte für storage und bootstrap/cache"
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache


# Übergabe an den Container-CMD (Apache/PHP-FPM)
exec "$@"
