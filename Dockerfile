# Multi-stage build for Laravel on Wasmer / Container Deploy
FROM php:8.2-cli-alpine

# Set working directory
WORKDIR /app

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql mbstring xml bcmath opcache fileinfo gd zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /app

# Install PHP dependencies without dev packages for production
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Prepare Laravel storage permissions and caching
RUN chmod -R 777 storage bootstrap/cache && chmod +x /app/docker-entrypoint.sh && php artisan storage:link || true

# Expose ports
EXPOSE 8080 10000

# Start Laravel with dynamic port support for Render / Cloud deploy
CMD ["/app/docker-entrypoint.sh"]
