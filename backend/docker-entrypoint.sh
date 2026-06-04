#!/bin/sh
set -e

cd /app

if [ -z "$DATABASE_URL" ]; then
  echo "FATAL: DATABASE_URL is not set. Add Neon connection string in Render Environment."
  exit 1
fi

# migrate runs via render.yaml preDeployCommand (faster health check on boot)
php artisan storage:link 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "Checking database connection..."
php artisan db:show 2>&1 || echo "WARN: database connection check failed (see logs above)"

echo "Running migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "WARN: migrate failed (see logs above)"

echo "Ensuring admin account exists..."
php artisan db:seed --class=AdminUserSeeder --force --no-interaction 2>&1 || echo "WARN: AdminUserSeeder failed (check ADMIN_EMAIL / ADMIN_PASSWORD in Render Environment)"

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
