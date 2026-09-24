#!/bin/sh
set -e

# Run migrations if database is accessible
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration notice: Database may still be initializing or table exists."
fi

# Ensure storage symlink exists
php artisan storage:link --force || true

# Clear old caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Start Laravel
echo "Starting Laravel server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
