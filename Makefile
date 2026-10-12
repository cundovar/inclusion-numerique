# Nom du projet (facultatif mais pratique pour l'affichage)
PROJECT_NAME=symfony_vue_docker

up:
	@echo "🚀 Lancement du projet $(PROJECT_NAME)..."
	docker compose up -d --build

down:
	@echo "🛑 Arrêt et suppression des conteneurs (volumes conservés)..."
	docker compose down --remove-orphans

down-volumes:
	@echo "⚠️  Arrêt et suppression des conteneurs ET des volumes (base MySQL incluse)..."
	docker compose down -v --remove-orphans

restart:
	@echo "🔄 Restart du projet..."
	make down
	make up

logs:
	@echo "📜 Logs de tous les conteneurs..."
	docker compose logs -f

bash:
	@echo "🖥️  Ouverture d'un shell dans le conteneur PHP..."
	docker compose exec php bash

front-bash:
	@echo "🖥️  Ouverture d'un shell dans le conteneur Front..."
	docker compose exec front sh

composer:
	@echo "📦 Composer install dans PHP..."
	docker compose exec php composer install

composer-require:
	@read -p "Quel package Composer installer ? " pkg; \
	docker compose exec php composer require $$pkg

npm-install:
	@echo "📦 npm install dans le Front..."
	docker compose exec front npm install

npm-dev:
	@echo "🔥 Lancement de Vite Dev Server (Front)..."
	docker compose exec front npm run dev

symfo:
	@read -p "Commande Symfony ? (ex: cache:clear) " cmd; \
	docker compose exec php php bin/console $$cmd

migrate:
	@echo "🗄️ Migration base de données..."
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

start:
	@echo "▶️  Démarrage des conteneurs..."
	docker compose start

stop:
	@echo "⏹️  Arrêt des conteneurs..."
	docker compose stop

dev:
	@echo "🔥 Démarrage en mode dev..."
	docker compose up -d && docker compose restart nginx


# --- Validation (mêmes contrôles que la CI) ---

lint-php:
	@echo "🔎 Syntaxe PHP..."
	docker compose exec -T php sh -c "find src tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null"

analyse:
	@echo "🔬 PHPStan..."
	docker compose exec -T php composer analyse -- --memory-limit=1G

test:
	@echo "🧪 PHPUnit..."
	docker compose exec -T php composer test

lint-front:
	@echo "🔎 ESLint..."
	docker compose exec -T front npm run lint

test-front:
	@echo "🧪 Vitest..."
	docker compose exec -T front npm test

test-e2e:
	@echo "🎭 Playwright (application locale démarrée et compilée requise)..."
	docker compose run --rm e2e

build-front:
	@echo "🏗️  Build Vite..."
	docker compose exec -T front npm run check

check-fast: lint-php analyse lint-front
	@echo "✅ check-fast terminé"

check: check-fast test test-front build-front
	@rm -f .agents/.needs-check
	@echo "✅ check terminé"

.PHONY: up down down-volumes restart logs bash front-bash composer composer-require npm-install npm-dev symfo migrate start stop dev \
	lint-php analyse test test-front test-e2e lint-front build-front check-fast check
