#!/bin/bash
set -e

cd /var/www/html

# Install composer dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo ">>> Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo ">>> Generating application key..."
    php artisan key:generate --force
fi

# Run database setup (MongoDB doesn't need migrations, but we seed)
echo ">>> Running database seeders..."
php artisan db:seed --force 2>/dev/null || echo "Seeder skipped (may already be seeded)"

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo ">>> Laravel app is ready!"

exec "$@"
