#!/bin/sh
set -e

cd /app

if [ -z "$DATABASE_URL" ]; then
  echo "FATAL: DATABASE_URL is not set. Add Neon connection string in Render Environment."
  exit 1
fi

# migrate runs via render.yaml preDeployCommand (faster health check on boot)
php artisan storage:link 2>/dev/null || true

# Do not config:cache — DATABASE_URL / secrets with special chars break cached config on Render
# routes/web.php uses closures — route:cache would crash startup on Render
php artisan view:cache

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
