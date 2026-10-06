# Official PHP 8.2 with Apache
FROM php:8.2-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    sqlite3 \
    zip \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . /var/www/html

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy and set execution permissions on entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose default port
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
