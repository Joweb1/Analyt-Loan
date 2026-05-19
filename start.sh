#!/bin/sh
set -e

# Navigate to the Laravel app directory
cd /var/www/laravel-app

# Clear old cache to prevent stale data
echo "Cleaning old cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize performance for production
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground
echo "Starting Apache..."
exec apache2-foreground
