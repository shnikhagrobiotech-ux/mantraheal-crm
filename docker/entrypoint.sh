#!/bin/bash
set -e

# Default PORT to 8080 if not provided by host environment (e.g. Render / Railway)
PORT="${PORT:-8080}"
export PORT

# Configure Apache port dynamically
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true

# Ensure all necessary storage and cache directories exist
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

# Prepare SQLite Database file if it does not exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database file..."
    touch /var/www/html/database/database.sqlite
fi

# Ensure APP_KEY exists and has base64 format
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "null" ]; then
    echo "Setting default Application Key..."
    export APP_KEY="base64:oADD47bw/Z5vH74kbx/utuECXi7my7NiSmBPu1m7nwA="
fi

# Run Database Migrations & Initial Seed
echo "Running migrations..."
php artisan migrate --force || true

echo "Seeding default records if database is fresh..."
php artisan db:seed --force || true

# CRITICAL for SQLite and File Sessions: Ensure www-data ownership and write permissions AFTER migrations
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 666 /var/www/html/database/database.sqlite 2>/dev/null || true

# Clear and rebuild cache
echo "Optimizing Laravel configuration..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Re-verify permissions for generated view caches
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Apache Web Server on port ${PORT}..."
exec apache2-foreground
