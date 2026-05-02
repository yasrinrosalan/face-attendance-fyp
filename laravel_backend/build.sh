#!/usr/bin/env bash

# Exit on error
set -o errexit

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node modules and build frontend (Tailwind/Bootstrap/etc)
npm install
npm run build

# Clear caches and compile
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Force run database migrations
php artisan migrate --force