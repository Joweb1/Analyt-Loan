#!/bin/sh
set -e

# Navigate to the Laravel app directory
cd /var/www/laravel-app

# Check migration status
echo "Checking database migration status..."
php artisan migrate:status --show-pending || echo "Warning: Could not verify migration status."

# Optimize performance
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground
echo "Starting Apache..."
exec apache2-foreground
