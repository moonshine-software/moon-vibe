#!/bin/sh
set -e

# Clear configurations to avoid caching issues in development
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

# Run the default command (e.g., php-fpm or bash)
exec "$@"