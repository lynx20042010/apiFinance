FROM php:8.3-fpm-alpine

# Installer dépendances système
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
    npm \
    dos2unix

# Installer Redis et extensions PHP
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail
WORKDIR /var/www

# Copier les fichiers de l’application
COPY --chown=www-data:www-data . .

# Convertir le script entrypoint au format Unix
RUN dos2unix docker-entrypoint.sh

# Copier le script d’entrée dans un emplacement global
RUN mv docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Installer dépendances PHP et Node
RUN composer install --no-dev --optimize-autoloader

RUN if [ -f package.json ]; then npm install && npm run build; else echo "No package.json found, skipping npm build"; fi

# Générer la clé d’application si absente
RUN if [ ! -f .env ]; then cp .env.example .env; fi && php artisan key:generate --no-interaction

# Réglages de permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exposer le port et lancer PHP-FPM
EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
