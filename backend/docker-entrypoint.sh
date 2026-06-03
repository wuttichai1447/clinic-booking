#!/bin/sh
set -e

if [ -n "$DATABASE_URL" ]; then
  php artisan migrate --force --no-interaction
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
