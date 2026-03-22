COMPOSE_DEV = docker compose -f docker-compose.yml -f docker-compose.dev.yml
COMPOSE_PROD = docker compose -f docker-compose.yml -f docker-compose.prod.yml
COMPOSE = $(COMPOSE_DEV)

init:
	cp -n .env.example .env
	cp .env src/.env
	$(COMPOSE) down -v --rmi all --remove-orphans
	$(COMPOSE) build --no-cache --pull
	$(COMPOSE) up -d
	rm -rf src/vendor
	$(COMPOSE) exec -u www-data app composer install
	$(COMPOSE) exec -u www-data app sh -c " \
		php artisan migrate:fresh --seed --force && \
		php artisan key:generate && \
		php artisan jwt:secret --force && \
		php artisan storage:link --force"
	cp src/.env .env
	$(COMPOSE) exec -u root app chown -R www-data:www-data /var/www/html
up:
	cp .env src/.env
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

analyse:
	$(COMPOSE) exec app vendor/bin/phpstan analyse --memory-limit=512M

baseline:
	$(COMPOSE) exec app vendor/bin/phpstan analyse --generate-baseline --memory-limit=512M

test:
	$(COMPOSE) exec -e XDEBUG_MODE=off app vendor/bin/phpunit

test-coverage:
	$(COMPOSE) exec -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-html coverage

ide-helper:
	$(COMPOSE) exec -u root app sh -c " \
		php artisan ide-helper:generate && \
		php artisan ide-helper:models --nowrite && \
		php artisan ide-helper:meta && \
		chown -R www-data:www-data /var/www/html"

telescope-clear:
	$(COMPOSE) exec app php artisan telescope:clear
