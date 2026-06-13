#!/bin/sh

# Wait for database to be ready
echo "Waiting for postgres..."
while ! nc -z postgres 5432; do
  sleep 1
done
echo "Postgres is up!"

# Wait for rabbitmq to be ready
echo "Waiting for rabbitmq..."
while ! nc -z rabbitmq 5672; do
  sleep 1
done
echo "RabbitMQ is up!"

# Install/update dependencies if needed
# composer install

# Run migrations
echo "Running migrations..."
php artisan migrate --force --seed

# Declare RabbitMQ queues
php artisan rabbitmq:declare

# Restart workers to refresh cache
php artisan queue:restart

# Run tests once (continue even if they fail)
echo "Running tests..."
php artisan test || echo "Tests failed, but starting application anyway..."

echo "Starting application..."

# Start the main process
exec "$@"
