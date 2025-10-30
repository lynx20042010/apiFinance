# API Finance - Gestion des Comptes Bancaires

Une API RESTful complète pour la gestion des comptes bancaires, clients et transactions financières avec authentification OAuth2.

## 🚀 Fonctionnalités

- ✅ Authentification JWT avec Laravel Sanctum
- ✅ Gestion complète des comptes bancaires (courant, épargne, chèque)
- ✅ Gestion des clients avec profils détaillés
- ✅ Système de rôles (Admin/Client)
- ✅ API RESTful avec documentation Swagger/OpenAPI
- ✅ Architecture microservices prête pour la production
- ✅ Support Docker complet
- ✅ Cache Redis et files d'attente
- ✅ Logs et monitoring

## 🛠️ Technologies

- **Framework**: Laravel 10
- **Langage**: PHP 8.2
- **Base de données**: PostgreSQL
- **Cache/Queue**: Redis
- **Serveur Web**: Nginx
- **Conteneurisation**: Docker & Docker Compose
- **Documentation**: Swagger/OpenAPI 3.0

## 📋 Prérequis

- Docker & Docker Compose
- Make (optionnel, pour utiliser les commandes du Makefile)

## 🚀 Installation et Démarrage

### Développement

1. **Cloner le projet**
   ```bash
   git clone <repository-url>
   cd api-finance
   ```

2. **Configuration**
   ```bash
   cp .env.example .env
   # Éditer .env avec vos paramètres
   ```

3. **Démarrage avec Docker**
   ```bash
   # Avec Make (recommandé)
   make setup

   # Ou manuellement
   docker-compose build --no-cache
   docker-compose up -d
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

4. **Accès à l'application**
   - API: http://localhost:8000
   - Documentation Swagger: http://localhost:8000/api/documentation

### Production

```bash
# Build et démarrage en production
make build-prod
make up-prod

# Ou manuellement
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d
```

## 📚 Documentation API

La documentation complète est disponible via Swagger UI :

**URL**: `http://localhost:8000/api/documentation`

### Endpoints Principaux

#### Authentification
- `POST /api/v1/auth/register` - Inscription
- `POST /api/v1/auth/login` - Connexion
- `POST /api/v1/auth/refresh` - Rafraîchir token
- `GET /api/v1/auth/me` - Profil utilisateur

#### Comptes (Admin seulement pour création/modification globale)
- `GET /api/v1/comptes` - Lister les comptes
- `POST /api/v1/comptes` - Créer un compte
- `GET /api/v1/comptes/{id}` - Détails d'un compte
- `PUT /api/v1/comptes/{id}` - Modifier un compte (clients: leurs comptes uniquement)
- `PUT /api/v1/admin/comptes/{id}` - Modifier n'importe quel compte (admin uniquement)
- `DELETE /api/v1/comptes/{id}` - Supprimer un compte

#### Opérations Spéciales (Admin uniquement)
- `POST /api/v1/comptes/{id}/block` - Bloquer un compte épargne
- `POST /api/v1/comptes/{id}/unblock` - Débloquer un compte épargne
- `POST /api/v1/comptes/{id}/archive` - Archiver un compte
- `POST /api/v1/comptes/{id}/unarchive` - Désarchiver un compte

## 🔧 Commandes Utiles

### Avec Make
```bash
make help           # Liste des commandes disponibles
make build          # Build des images Docker
make up             # Démarrer les conteneurs
make down           # Arrêter les conteneurs
make logs           # Voir les logs
make shell          # Accès shell du conteneur app
make db-shell       # Accès shell PostgreSQL
make test           # Exécuter les tests
make migrate        # Exécuter les migrations
make seed           # Seeder la base de données
make cache-clear    # Vider les caches
make clean          # Nettoyer complètement
```

### Avec Docker Compose
```bash
# Développement
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan test
docker-compose logs -f app

# Production
docker-compose -f docker-compose.prod.yml exec app php artisan migrate
docker-compose -f docker-compose.prod.yml logs -f
```

## 🧪 Tests

```bash
# Exécuter tous les tests
make test

# Avec couverture
docker-compose exec app php artisan test --coverage
```

## 🔒 Sécurité

- Authentification JWT avec Laravel Sanctum
- Autorisation basée sur les rôles (Admin/Client)
- Validation stricte des données d'entrée
- Protection CSRF
- Headers de sécurité HTTP
- Logs d'audit complets

## 📊 Architecture

```
api-finance/
├── app/                    # Code de l'application Laravel
├── config/                 # Configuration Laravel
├── database/               # Migrations et seeders
├── docker/                 # Configuration Docker
│   ├── nginx/             # Configuration Nginx
│   └── php/               # Configuration PHP
├── public/                # Assets publics
├── resources/             # Views et assets
├── routes/                # Définition des routes API
├── storage/               # Fichiers temporaires et logs
├── tests/                 # Tests unitaires et fonctionnels
├── docker-compose.yml     # Configuration développement
├── docker-compose.prod.yml # Configuration production
├── Dockerfile            # Image Docker de l'application
└── Makefile             # Commandes d'automatisation
```

## 🚀 Déploiement

### Variables d'environnement requises

```env
APP_NAME="API Finance"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false

# Base de données
DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=api_finance
DB_USERNAME=api_user
DB_PASSWORD=your-secure-password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=your-redis-password

# Cache et Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Commandes de déploiement

```bash
# Build de production
docker-compose -f docker-compose.prod.yml build

# Démarrage
docker-compose -f docker-compose.prod.yml up -d

# Migration de la base de données
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Génération de la documentation
docker-compose -f docker-compose.prod.yml exec app php artisan l5-swagger:generate
```

## 📈 Monitoring

- Logs Laravel centralisés
- Métriques de performance
- Health checks intégrés
- Monitoring des files d'attente Redis

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Support

Pour toute question ou problème :
- Ouvrir une issue sur GitHub
- Contacter l'équipe de développement
- Consulter la documentation Swagger

---

**Développé avec ❤️ par l'équipe API Finance**
