# API Finance - Gestion des Comptes Bancaires

Une API RESTful complète pour la gestion des comptes bancaires, clients et transactions financières avec authentification OAuth2.

## 🚀 Fonctionnalités

- ✅ Authentification JWT avec Laravel Sanctum
- ✅ Gestion complète des comptes bancaires (courant, épargne, chèque)
- ✅ Gestion des clients avec profils détaillés
- ✅ Système de rôles (Admin/Client)
- ✅ API RESTful avec documentation Swagger/OpenAPI
- ✅ Architecture microservices prête pour la production
- ✅ Cache et files d'attente
- ✅ Logs et monitoring
- ✅ Support multi-bases de données (PostgreSQL)

## 🛠️ Technologies

- **Framework**: Laravel 10
- **Langage**: PHP 8.2
- **Base de données**: PostgreSQL (multi-bases de données)
- **Cache/Queue**: File/Sync
- **Serveur Web**: Apache/Nginx
- **Documentation**: Swagger/OpenAPI 3.0

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- PostgreSQL
- Redis (optionnel, pour cache et queues - désactivé par défaut)
- Node.js & NPM (pour assets frontend si nécessaire)

## 🚀 Installation et Démarrage

### Installation Locale

1. **Cloner le projet**
   ```bash
   git clone <repository-url>
   cd api-finance
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration**
   ```bash
   cp .env.example .env
   # Éditer .env avec vos paramètres de base de données
   php artisan key:generate
   ```

4. **Configuration de la base de données**
   ```bash
   # Créer les bases de données PostgreSQL
   # Modifier config/database.php selon vos besoins
   php artisan migrate
   php artisan db:seed
   ```

5. **Démarrage du serveur**
   ```bash
   php artisan serve
   ```

6. **Accès à l'application**
   - API: http://localhost:8000
   - Documentation Swagger: http://localhost:8000/api/documentation

### Configuration Multi-Bases de Données

Le projet supporte plusieurs connexions de base de données :

```php
// Dans config/database.php
'connections' => [
    'render2' => [ // Base principale
        'host' => env('RENDER2_DB_HOST'),
        'database' => env('RENDER2_DB_DATABASE'),
        // ...
    ],
    'render3' => [ // Base secondaire (optionnelle)
        'host' => env('RENDER3_DB_HOST'),
        'database' => env('RENDER3_DB_DATABASE'),
        // ...
    ],
]
```

### Production

Pour le déploiement en production, configurez vos variables d'environnement et utilisez un serveur web comme Apache ou Nginx avec PHP-FPM.

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

### Commandes Laravel
```bash
# Migrations
php artisan migrate                    # Exécuter les migrations
php artisan migrate:rollback           # Annuler la dernière migration
php artisan migrate:fresh              # Reset complet de la DB

# Seeders
php artisan db:seed                    # Exécuter tous les seeders
php artisan db:seed --class=UserSeeder # Seeder spécifique

# Cache
php artisan cache:clear                # Vider le cache
php artisan config:clear               # Vider la config
php artisan route:clear                # Vider les routes
php artisan view:clear                 # Vider les vues

# Files d'attente (Queues)
php artisan queue:work                 # Traiter les jobs en file d'attente

# Tests
php artisan test                       # Exécuter tous les tests

# Documentation API
php artisan l5-swagger:generate        # Générer la documentation Swagger
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
│   ├── Models/            # Modèles Eloquent
│   ├── Http/Controllers/  # Contrôleurs API
│   ├── Jobs/             # Tâches en arrière-plan
│   └── Providers/        # Service Providers
├── config/                # Configuration Laravel
├── database/              # Migrations et seeders
├── public/                # Assets publics et index.php
├── resources/             # Views et assets (optionnel)
├── routes/                # Définition des routes API
├── storage/               # Logs, cache, sessions
├── tests/                 # Tests unitaires et fonctionnels
├── artisan               # Interface en ligne de commande Laravel
├── composer.json         # Dépendances PHP
└── README.md            # Cette documentation
```

## 🚀 Déploiement

### Variables d'environnement

```env
APP_NAME="API Finance"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false

# Base de données principale (render2)
RENDER2_DB_HOST=your-postgres-host
RENDER2_DB_DATABASE=your-database-name
RENDER2_DB_USERNAME=your-username
RENDER2_DB_PASSWORD=your-password
RENDER2_DB_PORT=5432

# Base de données secondaire (optionnelle)
RENDER3_DB_HOST=your-secondary-host
RENDER3_DB_DATABASE=your-secondary-db
RENDER3_DB_USERNAME=your-secondary-user
RENDER3_DB_PASSWORD=your-secondary-password
RENDER3_DB_PORT=5432

# Cache et Queue (Redis désactivé par défaut)
CACHE_STORE=array  # ou file/redis si disponible
QUEUE_CONNECTION=sync  # ou redis si disponible
SESSION_DRIVER=file  # ou redis si disponible
```

### Déploiement sur un serveur

1. **Transférer les fichiers**
   ```bash
   git clone your-repo /var/www/api-finance
   cd /var/www/api-finance
   ```

2. **Installer les dépendances**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configuration**
   ```bash
   cp .env.example .env
   # Éditer .env avec vos vraies valeurs
   php artisan key:generate
   ```

4. **Base de données**
   ```bash
   php artisan migrate --force
   php artisan db:seed
   ```

5. **Permissions**
   ```bash
   chown -R www-data:www-data /var/www/api-finance/storage
   chown -R www-data:www-data /var/www/api-finance/bootstrap/cache
   ```

6. **Optimisation**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan l5-swagger:generate
   ```

## 📈 Monitoring

- Logs Laravel centralisés
- Métriques de performance
- Health checks intégrés
- Monitoring des files d'attente

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
