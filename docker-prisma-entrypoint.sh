#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e 

echo "Starting Docker Prisma Entrypoint Script..."
npx prisma generate

echo "Running database migrations..."
npx prisma migrate deploy

# Only seed if SEED_DATABASE env var is set (e.g., for initial setup)
if [ "$SEED_DATABASE" = "true" ]; then
  echo "Seeding database..."
  npx prisma db seed
fi

echo "Starting application..."
# This runs the command passed to the container (like 'npm run dev')
exec "$@"