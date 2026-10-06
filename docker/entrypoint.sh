#!/bin/bash
set -e

# Default PORT to 8080 if not provided by host environment (e.g. Render / Railway)
PORT="${PORT:-8080}"
export PORT

# Configure Apache port dynamically
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true

# Prepare SQLite Database file if it does not exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database file..."
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage and cache directory permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Run Database Migrations & Initial Seed if database is fresh
echo "Running migrations..."
php artisan migrate --force

echo "Seeding default records if needed..."
php artisan db:seed --force || true

# Optimize configuration and views for production
echo "Caching Laravel configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Apache Web Server on port ${PORT}..."
exec apache2-foreground
