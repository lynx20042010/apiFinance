.PHONY: help build up down restart logs shell db-shell test clean

# Default target
help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

# Development commands
build: ## Build the Docker images
	docker-compose build --no-cache

up: ## Start the Docker containers
	docker-compose up -d

down: ## Stop the Docker containers
	docker-compose down

restart: ## Restart the Docker containers
	docker-compose restart

logs: ## Show logs from all containers
	docker-compose logs -f

shell: ## Access the app container shell
	docker-compose exec app sh

db-shell: ## Access the database shell
	docker-compose exec db psql -U api_user -d api_finance

# Production commands
build-prod: ## Build production Docker images
	docker-compose -f docker-compose.prod.yml build --no-cache

up-prod: ## Start production containers
	docker-compose -f docker-compose.prod.yml up -d

down-prod: ## Stop production containers
	docker-compose -f docker-compose.prod.yml down

# Testing and maintenance
test: ## Run PHP tests
	docker-compose exec app php artisan test

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

seed: ## Seed the database
	docker-compose exec app php artisan db:seed

fresh: ## Refresh database with seeders
	docker-compose exec app php artisan migrate:fresh --seed

cache-clear: ## Clear all Laravel caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Cleanup
clean: ## Remove all containers, volumes, and images
	docker-compose down -v --rmi all
	docker system prune -f

clean-prod: ## Remove production containers and volumes
	docker-compose -f docker-compose.prod.yml down -v --rmi all

# Quick setup for development
setup: build up migrate seed ## Full development setup