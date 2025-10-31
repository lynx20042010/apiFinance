FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    libzip-dev \
    nodejs \
    npm

# Clear cache
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and artisan for scripts
COPY composer.json composer.lock artisan ./

# Install PHP dependencies (without dev dependencies for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy existing application directory contents
COPY . /var/www

# Run composer scripts now that all files are available
RUN composer run-script post-autoload-dump --no-interaction

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

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

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]