# Dockerfile
FROM php:8.4-apache

# 1. Install system dependencies and unzip (crucial for unpacking the artifact)
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
# Using the standard mlocati installer script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions gd mbstring xml zip pdo_mysql pdo_pgsql opcache intl redis bcmath

# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 4. Configure Apache to allow .htaccess rewrites
RUN a2enmod rewrite

# 5. Setup Working Directory
WORKDIR /var/www

# 6. Copy the Zipped Project from the build context
# The CI/CD pipeline will place 'release.zip' here
COPY release.zip .

# 7. Unzip and Setup "Shared Hosting" Structure
RUN unzip -q release.zip -d laravel-app && \
    rm release.zip

# 8. Install PHP Dependencies
WORKDIR /var/www/laravel-app
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# 9. Move Public files to Main Directory (Apache Root)
WORKDIR /var/www
RUN rm -rf html/* && \
    cp -r laravel-app/public/* html/ && \
    cp laravel-app/public/.htaccess html/ || true

# 10. Verify Assets
RUN if [ ! -d "/var/www/html/build" ]; then \
    echo "ERROR: Vite build assets missing in /var/www/html/build. Check if 'npm run build' was successful." && exit 1; \
    fi

# 11. Modify index.php to point to the new paths
WORKDIR /var/www/html
RUN sed -i "s|require __DIR__.'/../vendor/autoload.php';|require __DIR__.'/../laravel-app/vendor/autoload.php';|g" index.php && \
    sed -i "s|\$app = require_once __DIR__.'/../bootstrap/app.php';|\$app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';|g" index.php

# 12. Create Production .env Base
RUN echo "APP_ENV=production" > ../laravel-app/.env && \
    echo "APP_DEBUG=false" >> ../laravel-app/.env && \
    echo "APP_IS_PRODUCTION=true" >> ../laravel-app/.env && \
    echo "LOG_CHANNEL=stderr" >> ../laravel-app/.env

# 13. Permissions and Storage Linking
RUN chown -R www-data:www-data /var/www/laravel-app \
    && chown -R www-data:www-data /var/www/html

RUN find /var/www/laravel-app -type d -exec chmod 755 {} + \
    && find /var/www/laravel-app -type f -exec chmod 644 {} +

RUN find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} +

RUN rm -rf /var/www/html/storage && \
    ln -s /var/www/laravel-app/storage/app/public /var/www/html/storage

# 14. Final Apache Permissions
RUN chown -h www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/laravel-app/storage /var/www/laravel-app/bootstrap/cache \
    && chmod -R 777 /var/www/laravel-app/storage /var/www/laravel-app/bootstrap/cache

# 15. Setup Startup Script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
