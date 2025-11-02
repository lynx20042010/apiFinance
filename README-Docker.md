# API Finance - Déploiement Docker

Ce guide explique comment déployer l'API Finance en utilisant Docker et Docker Compose.

## Prérequis

- Docker >= 20.10
- Docker Compose >= 2.0
- Make (optionnel, pour utiliser les commandes simplifiées)

## Architecture

L'application utilise une architecture multi-conteneurs :

- **App** : Application Laravel (PHP 8.1 + Apache)
- **DB** : Base de données PostgreSQL 13
- **Redis** : Cache et sessions

## Démarrage rapide

### Utilisation de Make (recommandé)

```bash
# Construire et démarrer tous les services
make build up

# Ou en mode développement
make dev
```

### Utilisation de Docker Compose

```bash
# Construire les images
docker-compose build

# Démarrer les services
docker-compose up -d

# Pour le développement (avec override)
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

## Accès aux services

- **Application** : http://localhost:8000
- **Documentation API** : http://localhost:8000/api/documentation
- **Base de données** : localhost:5432 (api_user / api_password)
- **Redis** : localhost:6379
- **MailHog** (dev) : http://localhost:8025

## Commandes utiles

### Gestion des conteneurs

```bash
# Arrêter les services
make down
# ou
docker-compose down

# Redémarrer
make restart

# Voir les logs
make logs
# ou
docker-compose logs -f app

# Accéder au shell du conteneur app
make shell
# ou
docker-compose exec app bash
```

### Base de données

```bash
# Exécuter les migrations
make migrate

# Alimenter la base avec des données de test
make seed

# Réinitialiser complètement la base
make fresh

# Se connecter à PostgreSQL
make db-connect
```

### Développement

```bash
# Exécuter les tests
make test

# Générer la documentation Swagger
make swagger

# Vider les caches
make cache-clear
```

## Configuration

### Variables d'environnement

Le fichier `.env.docker` contient la configuration optimisée pour Docker. Les variables importantes :

- `DB_HOST=db` : Nom du service PostgreSQL
- `REDIS_HOST=redis` : Nom du service Redis
- `APP_ENV=production` : Environnement de production
- `SEED_DB=true` : Alimenter la base avec des données de test

### Mode développement

Le fichier `docker-compose.override.yml` ajoute :

- Montage des volumes pour le développement
- Variables d'environnement de debug
- Service MailHog pour les emails

## Structure des fichiers

```
.
├── Dockerfile                 # Image de l'application
├── docker-compose.yml         # Configuration de production
├── docker-compose.override.yml # Configuration de développement
├── docker-entrypoint.sh       # Script de démarrage
├── .env.docker               # Variables d'environnement
├── Makefile                  # Commandes simplifiées
└── README-Docker.md          # Cette documentation
```

## Dépannage

### Problèmes courants

1. **Port déjà utilisé**
   ```bash
   # Changer les ports dans docker-compose.yml
   ports:
     - "8001:80"  # au lieu de 8000
   ```

2. **Erreur de build**
   ```bash
   # Nettoyer et reconstruire
   make clean
   make build
   ```

3. **Base de données inaccessible**
   ```bash
   # Vérifier que PostgreSQL est démarré
   make status

   # Voir les logs de la DB
   docker-compose logs db
   ```

4. **Permissions sur les fichiers**
   ```bash
   # Corriger les permissions
   sudo chown -R $USER:$USER .
   ```

### Logs et debugging

```bash
# Logs de tous les services
docker-compose logs

# Logs spécifiques
docker-compose logs app
docker-compose logs db

# Suivre les logs en temps réel
docker-compose logs -f
```

## Déploiement en production

Pour la production, ajustez les variables dans `docker-compose.yml` :

- `APP_ENV=production`
- `APP_DEBUG=false`
- `SEED_DB=false`
- Configurez les secrets appropriés

## Sécurité

- Ne jamais commiter `.env` dans Git
- Utilisez des mots de passe forts pour la DB
- En production, utilisez des secrets Docker
- Configurez HTTPS en reverse proxy

## Support

Pour toute question ou problème, consultez les logs ou vérifiez la configuration Docker.