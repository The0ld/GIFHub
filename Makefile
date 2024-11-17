# Variables
DOCKER_COMPOSE = docker compose
APP = $(DOCKER_COMPOSE) exec app

# Basic commands
build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

# Grouping related commands
setup: build up wait-for-db install key permissions migrate seed passport test ## Initial project setup

# Individual commands
install:
	$(APP) composer install

permissions:
	$(APP) chmod -R 775 storage bootstrap/cache

passport:
	$(APP) php artisan passport:install

key:
	$(APP) php artisan key:generate

migrate:
	$(APP) php artisan migrate

seed:
	$(APP) php artisan db:seed


test:
	$(APP) php artisan test

serve:
	$(APP) php artisan serve --host=0.0.0.0 --port=8000

wait-for-db:
	@sleep 5 && echo "Waiting for the database to initialize..."
