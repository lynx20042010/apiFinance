# ----------------------------------------
# Dockerfile Laravel + Apache + 2 PostgreSQL
# ----------------------------------------

# Use the official PHP image with Apache
FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-client \
    libpq-dev \
    redis-tools \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (without autoloader optimization for now)
RUN composer install --no-dev --no-scripts

# Copy the application code
COPY . /var/www/html
COPY --chown=www-data:www-data . /var/www/html

# Create .env if not exists
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate Laravel key if not exists
RUN php artisan key:generate --no-interaction

# Optimize autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Create startup script that waits for two databases
RUN cat <<'EOF' > /usr/local/bin/start.sh
#!/bin/bash
set -e

# Debug: print variables
echo "DB1_HOST=$RENDER2_DB_HOST"
echo "DB1_PORT=$RENDER2_DB_PORT"
echo "DB1_USER=$RENDER2_DB_USERNAME"

echo "DB2_HOST=$RENDER3_DB_HOST"
echo "DB2_PORT=$RENDER3_DB_PORT"
echo "DB2_USER=$RENDER3_DB_USERNAME"

# Wait for first database
until pg_isready -h "$RENDER2_DB_HOST" -p "$RENDER2_DB_PORT" -U "$RENDER2_DB_USERNAME"; do
    echo "Waiting for database 1..."
    sleep 2
done

# Wait for second database
until pg_isready -h "$RENDER3_DB_HOST" -p "$RENDER3_DB_PORT" -U "$RENDER3_DB_USERNAME"; do
    echo "Waiting for database 2..."
    sleep 2
done

# Migrations pour la base principale
php artisan migrate --database=render2 --force

# Migrations pour la base secondaire
php artisan migrate --database=render3 --force

# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
apache2-foreground
EOF

RUN chmod +x /usr/local/bin/start.sh

# Start the application
CMD ["/usr/local/bin/start.sh"]
