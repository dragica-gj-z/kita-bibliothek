#!/bin/sh
set -e

echo "Warte auf Datenbank & führe Migrationen aus..."

max_retries=10
counter=1

while [ "$counter" -le "$max_retries" ]
do
  if php artisan migrate --force; then
    echo "Migrationen erfolgreich."
    break
  fi

  echo "Migrationen fehlgeschlagen (Versuch $counter/$max_retries). Warte 5 Sekunden..."
  counter=$((counter+1))
  sleep 5
done

echo "Starte Apache..."
exec "$@"
