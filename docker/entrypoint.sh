#!/usr/bin/env bash
# Robust Laravel Entrypoint
# - Composer install (idempotent)
# - optionaler Vite-Build (inkl. devDependencies)
# - DB-Warte-Logik
# - Migrationen mit Retry
# - Seeder optional
# - Caches & Storage-Link
# - Startet am Ende Apache im Vordergrund

set -e

# ----------------------------- Konfiguration per ENV -----------------------------
APP_DIR="/var/www/html"

: "${BUILD_ASSETS:=true}"              # npm build bei Start ausführen (true/false)
: "${FORCE_ASSETS_BUILD:=false}"       # build erzwingen, auch wenn manifest existiert
: "${NODE_ENV:=production}"            # default für Build-Phase; Install überschreibt temporär
: "${CI:=true}"                        # npm im CI-Modus (ohne Interaktivität)

: "${DB_HOST:=}"                       # wenn gesetzt, wird auf DB gewartet
: "${DB_PORT:=3306}"
: "${DB_WAIT_TIMEOUT:=60}"             # max Sekunden fürs Warten auf DB

: "${LARAVEL_MIGRATE_ON_START:=true}"  # Migrationen ausführen (true/false)
: "${MIGRATE_RETRIES:=10}"             # wie oft Migration versuchen
: "${MIGRATE_RETRY_DELAY:=3}"          # Sekunden zwischen Versuchen

: "${RUN_SEEDERS:=true}"               # Seeder ausführen (true/false)
# -------------------------------------------------------------------------------

cd "$APP_DIR"

log()  { echo -e ">> $*"; }
warn() { echo -e "!! $*" >&2; }

# 1) .env anlegen, falls fehlt
if [ ! -f .env ] && [ -f .env.example ]; then
  log "No .env found — copying from .env.example"
  cp .env.example .env
fi

# 2) Composer install (ohne dev, optimiert) – idempotent
if [ -f composer.json ]; then
  log "composer install"
  composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
else
  log "No composer.json found – skipping composer install"
fi

# 3) Vite / Assets Build (optional & idempotent)
if [ "${BUILD_ASSETS}" = "true" ] && [ -f package.json ]; then
  need_build="false"
  if [ ! -f public/build/manifest.json ]; then
    need_build="true"
  fi
  if [ "${FORCE_ASSETS_BUILD}" = "true" ]; then
    need_build="true"
  fi

  if [ "${need_build}" = "true" ]; then
    log "Building front-end assets (install devDependencies + production build)"
    # devDependencies installieren, unabhängig vom globalen NODE_ENV
    if [ -f package-lock.json ]; then
      NODE_ENV= npm ci --no-audit --no-fund
    else
      NODE_ENV= npm install --no-audit --no-fund
    fi

    # Production-Build erzeugen (z. B. Vite)
    if NODE_ENV=production npm run build; then
      log "Assets built successfully."
    else
      warn "Asset build failed"; exit 1
    fi
  else
    log "Skipping asset build (public/build/manifest.json exists and FORCE_ASSETS_BUILD=false)."
  fi
else
  log "Skipping asset build (BUILD_ASSETS=false or no package.json)."
fi
  
# 4) Rechte fixen (idempotent)
if [ -d storage ] && [ -d bootstrap/cache ]; then
  log "Fixing permissions for storage/ and bootstrap/cache/"
  chown -R www-data:www-data storage bootstrap/cache || true
  chmod -R ug+rwx storage bootstrap/cache || true
fi

# 5) Auf DB warten (nur wenn DB_HOST gesetzt ist)
if [ -n "${DB_HOST}" ]; then
  log "Waiting for database at ${DB_HOST}:${DB_PORT} (timeout ${DB_WAIT_TIMEOUT}s)"
  waited=0
  until nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1; do
    sleep 1
    waited=$((waited+1))
    if [ "${waited}" -ge "${DB_WAIT_TIMEOUT}" ]; then
      warn "Database did not become ready within ${DB_WAIT_TIMEOUT}s"
      break
    fi
  done
  if nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1; then
    log "Database is up."
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
