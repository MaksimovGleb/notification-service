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
php artisan migrate --force

# Run tests before starting the app
echo "Running tests..."
php artisan test
if [ $? -ne 0 ]; then
    echo "Tests failed! Exiting..."
    exit 1
fi

echo "Tests passed! Starting application..."

# Start the main process
exec "$@"
