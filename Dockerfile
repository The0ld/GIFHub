# Using a base image with PHP 8.2 and FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files to the container
COPY . .

# Permissions for storage and cache
RUN chmod -R 775 storage bootstrap/cache

# Install Laravel Dependencies
RUN composer install --optimize-autoloader --no-dev

# Expose PHP-FPM and laravel developnet port
EXPOSE 9000 8000

# Default command to start PHP-FPM
CMD ["php-fpm"]

