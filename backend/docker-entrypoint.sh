#!/bin/sh
set -e

cd /app

# migrate runs via render.yaml preDeployCommand (faster health check on boot)
php artisan storage:link 2>/dev/null || true

php artisan config:cache
# routes/web.php uses closures — route:cache would crash startup on Render
php artisan view:cache

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
