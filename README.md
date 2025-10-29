# API Finance - Gestion des Comptes Bancaires

Une API RESTful moderne développée avec Laravel 10 pour la gestion complète des comptes bancaires et transactions financières.

## 🚀 Fonctionnalités

### Gestion des Clients
- ✅ Création automatique de clients avec génération de comptes
- ✅ Gestion des informations personnelles (nom, email, téléphone, adresse)
- ✅ Authentification automatique avec génération de mot de passe temporaire
- ✅ Support des clients particuliers et entreprises

### Gestion des Comptes
- ✅ Création de comptes (courant, épargne, titre, devise)
- ✅ Gestion des soldes et devises multiples (XAF, EUR, USD, CAD, GBP)
- ✅ Statuts de comptes (actif, inactif, bloqué, fermé, archivé)
- ✅ Numéros de compte uniques générés automatiquement

### Gestion des Transactions
- ✅ Types de transactions : dépôt, retrait, virement, transfert, commission, intérêt
- ✅ Suivi des statuts (en attente, traitée, annulée, échouée)
- ✅ Historique complet avec métadonnées

### Opérations Avancées
- ✅ Blocage/déblocage des comptes épargne
- ✅ Archivage des comptes fermés
- ✅ Suppression sécurisée avec vérifications

## 🏗️ Architecture

### Base de Données (PostgreSQL)
```
User (UUID) ────1:N─── Client (UUID)
    │                     │
    │                     │
    └──1:1─── Admin       └──1:N─── Compte (UUID)
                              │
                              └──1:N─── Transaction (UUID)
```

### Technologies Utilisées
- **Laravel 10** - Framework PHP moderne
- **PostgreSQL** - Base de données robuste
- **Laravel Passport** - Authentification OAuth2
- **Laravel Debugbar** - Outil de débogage (désactivé en prod)
- **Swagger/OpenAPI** - Documentation interactive
- **Docker** - Conteneurisation

## 📋 Prérequis

- PHP 8.1+
- Composer
- PostgreSQL 12+
- Docker & Docker Compose (optionnel)

## 🛠️ Installation

### Installation Locale (avec Docker)

1. **Cloner le projet**
```bash
git clone <repository-url>
cd apiFinance
```

2. **Configuration Docker**
```bash
# Le docker-compose.yml est déjà configuré
docker compose up --build
```

3. **Configuration de la base de données**
```bash
# Dans le container Docker
php artisan migrate
php artisan passport:install
```

### Installation Traditionnelle

1. **Installation des dépendances**
```bash
composer install
```

2. **Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Base de données**
```bash
# Configurer PostgreSQL dans .env
php artisan migrate
php artisan passport:install
```

## ⚙️ Configuration

### Variables d'Environnement (.env)

```env
# Application
APP_NAME=apiFinance
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de données PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=dpg-d40bs9jipnbc73cirh4g-a.oregon-postgres.render.com
DB_PORT=5432
DB_DATABASE=apifinacedb
DB_USERNAME=apifinacedb_user
DB_PASSWORD=vqZVTXI4pkrE6Txg4Ell6McKz7qJncj9

# Debugbar (désactivé en production)
DEBUGBAR_ENABLED=false
```

## 📚 Documentation API

### Accès à la Documentation
- **Production** : `https://apifinance.onrender.com/ndeyendiaye/documentation`
- **Local** : `http://localhost:8000/docs`

### Authentification
L'API utilise OAuth2 avec Laravel Passport :
- **Client Personnel** : Pour les applications mobiles
- **Client Mot de Passe** : Pour l'authentification utilisateur

## 🔌 Endpoints API

### Comptes (`/api/v1/comptes`)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/v1/comptes` | Lister les comptes (avec pagination/filtres) |
| POST | `/api/v1/comptes` | Créer un nouveau compte |
| GET | `/api/v1/comptes/{id}` | Détails d'un compte |
| PUT | `/api/v1/comptes/{id}` | Modifier un compte |
| POST | `/api/v1/comptes/{id}/block` | Bloquer un compte épargne |
| POST | `/api/v1/comptes/{id}/unblock` | Débloquer un compte épargne |
| POST | `/api/v1/comptes/{id}/archive` | Archiver un compte fermé |
| POST | `/api/v1/comptes/{id}/unarchive` | Désarchiver un compte |
| DELETE | `/api/v1/comptes/{id}` | Supprimer un compte |

### Exemple de Création de Compte

```bash
curl -X POST https://apifinance.onrender.com/api/v1/comptes \
  -H "Content-Type: application/json" \
  -d '{
    "type": "courant",
    "soldeInitial": 50000,
    "devise": "XAF",
    "client": {
      "titulaire": "Jean Dupont",
      "email": "jean@example.com",
      "telephone": "+221771234567",
      "adresse": "Dakar, Sénégal"
    }
  }'
```

**Réponse :**
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "numeroCompte": "CPT2025000001",
    "titulaire": "Jean Dupont",
    "type": "courant",
    "solde": 50000,
    "devise": "XAF",
    "statut": "actif",
    "dateCreation": "2025-10-29T06:46:50Z"
  }
}
```

## 🧪 Tests

### Exécution des Tests
```bash
php artisan test
```

### Tests Disponibles
- ✅ Tests des modèles (Client, Compte, Transaction)
- ✅ Tests des contrôleurs API
- ✅ Tests des factories
- ✅ Tests des requêtes de validation

## 🚀 Déploiement

### Sur Render
Le projet est configuré pour le déploiement sur Render avec :
- ✅ `render.yaml` pour la configuration
- ✅ Docker support
- ✅ Variables d'environnement
- ✅ Base de données PostgreSQL externe

### Commandes de Déploiement
```bash
# Build et déploiement
docker build -t apifinance .
docker run -p 8000:8000 apifinance
```

## 🔒 Sécurité

- ✅ Authentification OAuth2 avec Passport
- ✅ Validation stricte des données d'entrée
- ✅ Protection CSRF
- ✅ Sanitisation des entrées
- ✅ Logs d'audit pour les opérations sensibles

## 📊 Monitoring

- ✅ Laravel Debugbar (développement uniquement)
- ✅ Logs structurés
- ✅ Métriques de performance
- ✅ Gestion d'erreurs complète

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
- 📧 Email : support@apifinance.com
- 📚 Documentation : `https://apifinance.onrender.com/ndeyendiaye/documentation`
- 🐛 Issues : GitHub Issues

---

**Développé avec ❤️ par l'équipe API Finance**
