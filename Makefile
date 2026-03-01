COMPOSE_DEV = docker compose -f docker-compose.yml -f docker-compose.dev.yml
COMPOSE_PROD = docker compose -f docker-compose.yml -f docker-compose.prod.yml
COMPOSE = $(COMPOSE_DEV)

init:
	$(COMPOSE) down -v
	$(COMPOSE) build
	$(COMPOSE) up -d
	$(COMPOSE) exec app php artisan migrate:fresh --seed --force

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

down-v:
	$(COMPOSE) down -v

build:
	$(COMPOSE) build

restart:
	$(COMPOSE) restart

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

migrate:
	$(COMPOSE) exec app php artisan migrate --force

migrate-fresh:
	$(COMPOSE) exec app php artisan migrate:fresh --seed --force

artisan:
	$(COMPOSE) exec app php artisan $(cmd)

tinker:
	$(COMPOSE) exec app php artisan tinker

bash:
	$(COMPOSE) exec app sh

db:
	$(COMPOSE) exec db mariadb -u $${DB_USERNAME:-cinemacloud} -p$${DB_PASSWORD:-secret} $${DB_DATABASE:-cinemacloud}

lint:
	$(COMPOSE) exec app vendor/bin/php-cs-fixer fix --dry-run --diff

fix:
	$(COMPOSE) exec app vendor/bin/php-cs-fixer fix

test:
	$(COMPOSE) exec -e XDEBUG_MODE=off app vendor/bin/phpunit

test-coverage:
	$(COMPOSE) exec -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-html coverage