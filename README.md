# CinemaCloud — Backend

Laravel 11 REST API running in Docker (PHP-FPM + Nginx + MariaDB).

## Requirements

- Docker & Docker Compose
- A `src/.env` file (copy from `src/.env.example` and fill in the values)

## Quick start

```bash
make init
```

Tears down any existing containers/volumes, rebuilds images, starts services, and runs a fresh migration with seed data.

## Makefile commands

| Command | Description |
|---|---|
| `make init` | Full reset: down -v → build → up → migrate:fresh --seed |
| `make up` | Start all containers in the background |
| `make down` | Stop and remove containers |
| `make down-v` | Stop and remove containers **and volumes** |
| `make build` | Rebuild Docker images |
| `make restart` | Restart all containers |
| `make ps` | List running containers |
| `make logs` | Tail logs from all containers |
| `make migrate` | Run pending migrations |
| `make migrate-fresh` | Drop all tables, re-run migrations and seed |
| `make artisan cmd=<command>` | Run any Artisan command, e.g. `make artisan cmd="route:list"` |
| `make tinker` | Open Laravel Tinker |
| `make bash` | Open a bash shell inside the app container |
| `make db` | Open a MariaDB shell |
