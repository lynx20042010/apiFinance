# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    --no-install-recommends \
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
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurer Apache
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances PHP (sans interaction)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configuration Apache personnalisée
RUN printf "<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

# Créer le script de démarrage
RUN printf '#!/bin/bash\n\
set -e\n\
echo "Waiting for database..."\n\
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; do\n\
    sleep 1\n\
done\n\
echo "Database is ready!"\n\
\n\
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
if [ "$RUN_MIGRATIONS" = "true" ]; then\n\
    echo "Running migrations..."\n\
    php artisan migrate --force || true\n\
fi\n\
\n\
if [ -f artisan ]; then\n\
    echo "Generating Swagger documentation..."\n\
    php artisan l5-swagger:generate || true\n\
fi\n\
\n\
echo "Starting Apache..."\n\
exec apache2-foreground\n' > /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

# Commande de démarrage
CMD ["/usr/local/bin/start.sh"]
