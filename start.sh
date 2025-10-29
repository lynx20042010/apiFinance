#!/bin/bash

# Script de démarrage pour Render
set -e

echo "🚀 Démarrage de l'application apiFinance..."

# Créer le fichier .env s'il n'existe pas
if [ ! -f .env ]; then
    echo "📄 Création du fichier .env..."
    cp .env.example .env
fi

# Générer la clé d'application si elle n'existe pas
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Génération de la clé d'application..."
    php artisan key:generate --force
fi

# Attendre que la base de données soit prête
echo "⏳ Attente de la base de données..."
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; then
        echo "✅ Base de données prête !"
        break
    fi

    echo "Tentative $attempt/$max_attempts - Base de données non prête, attente..."
    sleep 2
    ((attempt++))
done

if [ $attempt -gt $max_attempts ]; then
    echo "❌ Impossible de se connecter à la base de données après $max_attempts tentatives"
    exit 1
fi

# Générer la clé d'application si elle n'existe pas
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Génération de la clé d'application..."
    php artisan key:generate --force
fi

# Exécuter les migrations
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

# Générer la documentation Swagger
echo "📚 Génération de la documentation Swagger..."
php artisan l5-swagger:generate

# Optimiser l'application pour la production
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Démarrer le serveur web
echo "🌐 Démarrage du serveur web..."
exec apache2-foreground