.PHONY: help build up down restart logs shell test clean

# Couleurs pour les messages
GREEN := \033[0;32m
BLUE := \033[0;34m
YELLOW := \033[1;33m
NC := \033[0m # No Color

help: ## Afficher cette aide
	@echo "$(BLUE)API Finance - Commandes Docker$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}'

build: ## Construire les images Docker
	@echo "$(BLUE)🔨 Construction des images Docker...$(NC)"
	docker-compose build --no-cache

up: ## Démarrer tous les services
	@echo "$(GREEN)🚀 Démarrage des services...$(NC)"
	docker-compose up -d
	@echo "$(YELLOW)⏳ Attente du démarrage complet...$(NC)"
	@sleep 10
	@echo "$(GREEN)✅ Services démarrés !$(NC)"
	@echo "$(BLUE)🌐 Application: http://localhost:8000$(NC)"
	@echo "$(BLUE)📖 API Docs: http://localhost:8000/api/documentation$(NC)"

dev: ## Démarrer en mode développement (avec override)
	@echo "$(GREEN)🚀 Démarrage en mode développement...$(NC)"
	docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
	@echo "$(YELLOW)⏳ Attente du démarrage complet...$(NC)"
	@sleep 15
	@echo "$(GREEN)✅ Services de développement démarrés !$(NC)"
	@echo "$(BLUE)🌐 Application: http://localhost:8000$(NC)"
	@echo "$(BLUE)📖 API Docs: http://localhost:8000/api/documentation$(NC)"
	@echo "$(BLUE)📧 MailHog: http://localhost:8025$(NC)"

down: ## Arrêter tous les services
	@echo "$(YELLOW)🛑 Arrêt des services...$(NC)"
	docker-compose down

restart: down up ## Redémarrer tous les services

logs: ## Afficher les logs des services
	docker-compose logs -f app

shell: ## Accéder au shell du conteneur app
	docker-compose exec app bash

test: ## Exécuter les tests
	docker-compose exec app php artisan test

migrate: ## Exécuter les migrations
	docker-compose exec app php artisan migrate

seed: ## Exécuter les seeders
	docker-compose exec app php artisan db:seed

fresh: ## Réinitialiser la base de données
	docker-compose exec app php artisan migrate:fresh --seed

cache-clear: ## Vider tous les caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

swagger: ## Générer la documentation Swagger
	docker-compose exec app php artisan l5-swagger:generate

clean: down ## Nettoyer les conteneurs et volumes
	@echo "$(YELLOW)🧹 Nettoyage des ressources Docker...$(NC)"
	docker-compose down -v
	docker system prune -f
	docker volume prune -f

status: ## Afficher le statut des services
	@echo "$(BLUE)📊 Statut des services:$(NC)"
	docker-compose ps

db-connect: ## Se connecter à la base de données
	docker-compose exec db psql -U api_user -d api_finance
