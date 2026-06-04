#!/bin/sh
set -e

cd /app

if [ -n "$DATABASE_URL" ]; then
  echo "Running migrations..."
  php artisan migrate --force --no-interaction
fi

php artisan storage:link 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
