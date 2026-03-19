# ─────────────────────────────────────────────────────────────────────────────
# AFDA Multi-Tenant eCommerce — Makefile
# ─────────────────────────────────────────────────────────────────────────────

.PHONY: help up down build restart logs shell-app shell-mongo seed test init

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Docker ────────────────────────────────────────────────────────────────────

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

build: ## Build and start containers (fresh)
	docker-compose up -d --build

restart: ## Restart all containers
	docker-compose restart

logs: ## Follow logs
	docker-compose logs -f

logs-app: ## Follow Laravel app logs
	docker-compose logs -f app

# ── Setup ──────────────────────────────────────────────────────────────────────

init: ## First-time setup: build, copy .env, install deps, seed
	@echo ">>> Building containers..."
	docker-compose up -d --build
	@echo ">>> Waiting for MongoDB to be ready..."
	timeout /t 5 /nobreak >nul
	@echo ">>> Copying .env file..."
	-docker-compose exec -T app cp .env.example .env
	@echo ">>> Generating app key..."
	docker-compose exec -T app php artisan key:generate
	@echo ">>> Running seeders..."
	docker-compose exec -T app php artisan db:seed
	@cmd /c echo.
	@echo Setup complete!
	@echo    Backend:       http://localhost:8000
	@echo    Frontend:      http://localhost:5173
	@echo    Mongo Express: http://localhost:8081

# ── Laravel ────────────────────────────────────────────────────────────────────

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

tinker: ## Open Laravel Tinker REPL
	docker-compose exec app php artisan tinker

shell-app: ## Shell into PHP container
	docker-compose exec app bash

# ── Tests ──────────────────────────────────────────────────────────────────────

test: ## Run all PHPUnit tests
	docker-compose exec app php artisan test

test-unit: ## Run unit tests only
	docker-compose exec app php artisan test --testsuite=Unit

test-feature: ## Run feature tests only
	docker-compose exec app php artisan test --testsuite=Feature

# ── Database ───────────────────────────────────────────────────────────────────

shell-mongo: ## Open MongoDB shell
	docker-compose exec mongodb mongosh -u root -p secret

# ── Frontend ───────────────────────────────────────────────────────────────────

frontend-install: ## Install frontend npm dependencies
	docker-compose exec frontend npm install

frontend-build: ## Build frontend for production
	docker-compose exec frontend npm run build
