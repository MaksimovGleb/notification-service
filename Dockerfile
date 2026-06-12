FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    postgresql-dev \
    sqlite-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pdo_sqlite sockets gd zip

# Install redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod +x /var/www/docker-entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
CMD ["php-fpm"]
