#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")"

git pull --ff-only origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

npm ci
npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment completed."