# CinemaCloud — Backend

Laravel 12 REST API running in Docker (PHP-FPM + Nginx + MariaDB + Redis).

## Requirements
- Docker & Docker Compose
- A `.env` file in the project root (copy from `.env.example` and configure) — created automatically when running `make init`

## Quick start

```bash
make init
```

Tears down any existing containers/volumes, rebuilds images, starts services, generates the app key, JWT secret, links storage, and runs a fresh migration with seed data. The `.env` file is automatically copied from `.env.example` if it doesn't exist yet.

## Makefile commands

### Container Management

| Command        | Description                                             |
|----------------|---------------------------------------------------------|
| `make init`    | Full reset: down -v → build → up → migrate:fresh --seed |
| `make up`      | Start all containers in the background                  |
| `make down`    | Stop and remove containers                              |
| `make down-v`  | Stop and remove containers **and volumes**              |
| `make build`   | Rebuild Docker images                                   |
| `make restart` | Restart all containers                                  |
| `make ps`      | List running containers                                 |
| `make logs`    | Tail logs from all containers                           |

### Database

| Command              | Description                                 |
|----------------------|---------------------------------------------|
| `make migrate`       | Run pending migrations                      |
| `make migrate-fresh` | Drop all tables, re-run migrations and seed |
| `make db`            | Open a MariaDB shell                        |

### Laravel

| Command                      | Description                                                   |
|------------------------------|---------------------------------------------------------------|
| `make artisan cmd=<command>` | Run any Artisan command, e.g. `make artisan cmd="route:list"` |
| `make tinker`                | Open Laravel Tinker                                           |
| `make bash`                  | Open a shell inside the app container                         |

### Code Quality

| Command              | Description                         |
|----------------------|-------------------------------------|
| `make lint`          | Check code style (dry-run)          |
| `make fix`           | Fix code style issues automatically |
| `make test`          | Run PHPUnit tests                   |
| `make test-coverage` | Run tests with HTML coverage report |

### IDE Support

| Command           | Description                                                                        |
|-------------------|------------------------------------------------------------------------------------|
| `make ide-helper` | Generate IDE helper files (`_ide_helper.php`, model PHPDocs, `.phpstorm.meta.php`) |

> Requires `barryvdh/laravel-ide-helper`. Run this after adding new models, facades, or service container bindings to
> keep PhpStorm type hints accurate.

## CI/CD

GitHub Actions workflow automatically runs on push/PR to `main`:

- Installs dependencies
- Sets up MariaDB and Redis
- Runs migrations
- Executes PHPUnit tests

## Tech Stack

- **PHP 8.5** with OPcache, Xdebug
- **Laravel 12**
- **MariaDB 12**
- **Redis 8.6**
- **Nginx** (reverse proxy)
- **Docker Compose** for orchestration