.PHONY: up down restart build ps logs shell artisan test migrate fresh seed rollback composer setup key storage-link lint npm

export DC = docker compose

# Docker Compose lifecycle
up:
	$(DC) up -d

down:
	$(DC) down

restart:
	$(DC) restart

build:
	$(DC) build

ps:
	$(DC) ps

logs:
	$(DC) logs -f

# App container
shell:
	$(DC) exec app sh

artisan:
	$(DC) exec app php artisan $(cmd)

composer:
	$(DC) exec app composer $(cmd)

# Database
test:
	$(DC) run --rm app php artisan test --compact

migrate:
	$(DC) exec app php artisan migrate

fresh:
	$(DC) exec app php artisan migrate:fresh --seed

seed:
	$(DC) exec app php artisan db:seed

rollback:
	$(DC) exec app php artisan migrate:rollback

# Setup
setup: key storage-link

key:
	$(DC) exec app php artisan key:generate

storage-link:
	$(DC) exec app php artisan storage:link

# Frontend
npm:
	$(DC) exec frontend npm $(cmd)

# Analysis
lint:
	$(DC) run --rm app ./vendor/bin/phpstan analyse --memory-limit=512M
