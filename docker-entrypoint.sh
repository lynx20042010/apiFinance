#!/bin/bash
set -e

echo "🚀 Starting API Finance Docker container..."

# Attendre que PostgreSQL soit prêt
echo "⏳ Waiting for PostgreSQL to be ready..."
while ! pg_isready -h ${DB_HOST:-db} -p ${DB_PORT:-5432} -U ${DB_USERNAME:-api_user}; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done
echo "✅ PostgreSQL is ready!"

# Attendre que Redis soit prêt
echo "⏳ Waiting for Redis to be ready..."
while ! redis-cli -h ${REDIS_HOST:-redis} -p ${REDIS_PORT:-6379} ping > /dev/null 2>&1; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "✅ Redis is ready!"

# Copier le fichier .env si nécessaire
if [ ! -f .env ]; then
    echo "📋 Copying environment file..."
    cp .env.docker .env
fi

# Générer la clé d'application si elle n'existe pas
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Configurer les permissions
echo "🔒 Setting up permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Nettoyer le cache
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Exécuter les migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Exécuter les seeders si demandé
if [ "${SEED_DB:-false}" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
fi

# Générer la documentation Swagger si demandé
if [ "${GENERATE_SWAGGER:-false}" = "true" ]; then
    echo "📚 Generating Swagger documentation..."
    php artisan l5-swagger:generate
fi

# Optimiser l'application pour la production
echo "⚡ Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 API Finance is ready!"
echo "🌐 Application will be available at: http://localhost:8000"
echo "📖 API Documentation: http://localhost:8000/api/documentation"

# Démarrer Apache
echo "🌟 Starting Apache web server..."
exec apache2-foreground