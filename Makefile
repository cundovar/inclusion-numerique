# Nom du projet (facultatif mais pratique pour l'affichage)
PROJECT_NAME=symfony_vue_docker

up:
	@echo "🚀 Lancement du projet $(PROJECT_NAME)..."
	docker-compose up -d --build

down:
	@echo "🛑 Arrêt et suppression des conteneurs..."
	docker-compose down -v --remove-orphans

restart:
	@echo "🔄 Restart du projet..."
	make down
	make up

logs:
	@echo "📜 Logs de tous les conteneurs..."
	docker-compose logs -f

bash:
	@echo "🖥️  Ouverture d'un shell dans le conteneur PHP..."
	docker-compose exec php bash

front-bash:
	@echo "🖥️  Ouverture d'un shell dans le conteneur Front..."
	docker-compose exec front sh

composer:
	@echo "📦 Composer install dans PHP..."
	docker-compose exec php composer install

composer-require:
	@read -p "Quel package Composer installer ? " pkg; \
	docker-compose exec php composer require $$pkg

npm-install:
	@echo "📦 npm install dans le Front..."
	docker-compose exec front npm install

npm-dev:
	@echo "🔥 Lancement de Vite Dev Server (Front)..."
	docker-compose exec front npm run dev

symfo:
	@read -p "Commande Symfony ? (ex: cache:clear) " cmd; \
	docker-compose exec php php bin/console $$cmd

migrate:
	@echo "🗄️ Migration base de données..."
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

