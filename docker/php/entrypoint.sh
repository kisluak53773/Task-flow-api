#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
    echo "Vendor folder not found. Installing Composer dependencies..."
    composer install --no-interaction
fi

echo "Ensuring Laravel storage directories exist..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

echo "Applying secure permissions to storage and bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 755 {} \;
find storage bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null || true

exec "$@"