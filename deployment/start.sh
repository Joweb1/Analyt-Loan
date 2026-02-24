#!/bin/sh
set -e

# Navigate to the Laravel app directory
cd /var/www/laravel-app

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || { echo "Migrations failed!"; exit 1; }

# Optimize performance
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground
echo "Starting Apache..."
exec apache2-foreground
