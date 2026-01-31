# Include the .env file so we can use its variables here if needed
-include .env
# 1. Start the containers in the background (detached)
up:
	docker compose up -d

# 2. Stop and remove containers
down:
	docker compose down

# 3. Rebuild and start (useful when you change the Dockerfile or package.json)
build:
	docker compose up -d --build

# 4. Restart the services
restart:
	docker compose restart

# 5. View live logs from all containers
logs:
	docker compose logs -f

# 6. Enter the Node.js container's terminal
shell:
	docker compose exec app sh

# 7. Enter the MariaDB terminal directly (handy for manual SQL queries)
db-shell:
	docker compose exec -e MYSQL_PWD=$(DB_PASSWORD) db mariadb -u $(DB_USER) $(DB_NAME)
# 8. Clean up unused Docker stuff (volumes, images) to save space
clean:
	docker compose down -v