FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-client \
    libpq-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and artisan for scripts
COPY composer.json composer.lock artisan ./

# Install PHP dependencies (without dev dependencies for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy existing application directory contents
COPY . /var/www/html

# Set working directory to Apache root
WORKDIR /var/www/html

# Run composer scripts now that all files are available
RUN composer dump-autoload --optimize --no-interaction

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install Node.js dependencies and build assets (only if package.json exists)
RUN if [ -f package.json ]; then npm install && npm run build; else echo "No package.json found, skipping npm build"; fi

# Generate application key if not exists
RUN if [ ! -f .env ]; then cp .env.example .env; fi && php artisan key:generate --no-interaction

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database to be ready..."\n\
while ! pg_isready -h ${DB_HOST:-db} -p ${DB_PORT:-5432} -U ${DB_USERNAME:-api_user} > /dev/null 2>&1; do\n\
  echo "Database is unavailable - sleeping"\n\
  sleep 2\n\
done\n\
\n\
echo "Database is ready!"\n\
\n\
# Run migrations\n\
echo "Running database migrations..."\n\
php artisan migrate --force\n\
\n\
# Seed the database if SEED_DB is set to true\n\
if [ "$SEED_DB" = "true" ]; then\n\
    echo "Seeding database..."\n\
    php artisan db:seed --force\n\
fi\n\
\n\
# Generate application key if not exists\n\
if [ -z "$APP_KEY" ]; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Generate Swagger documentation\n\
echo "Generating API documentation..."\n\
php artisan l5-swagger:generate\n\
\n\
# Clear and cache config\n\
echo "Caching configuration..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Set proper permissions\n\
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache\n\
\n\
echo "Starting application..."\n\
exec "$@"' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Configure Apache for Laravel
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port 80 for Apache
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start Apache
CMD ["apache2-foreground"]