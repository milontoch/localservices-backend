#!/bin/bash

echo "Running post-deployment tasks..."

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache config
echo "Caching configuration..."
php artisan config:cache

# Cache routes
echo "Caching routes..."
php artisan route:cache

# Cache views
echo "Caching views..."
php artisan view:cache

# Clear expired cache
echo "Clearing expired cache..."
php artisan cache:prune-stale-tags

echo "Post-deployment tasks completed!"
