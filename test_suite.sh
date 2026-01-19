#!/bin/sh

# Run Pint for code style
./vendor/bin/pint

# Run PHPStan for static analysis
./vendor/bin/phpstan analyse

# Run Unit Tests
php artisan test --testsuite=Unit

# Run Feature Tests
php artisan test --testsuite=Feature
