FROM php:8.2-apache

# Install dependencies for PostgreSQL and other extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
# --no-dev: Skip dev dependencies
# --optimize-autoloader: Generate optimized autoloader
RUN composer install --no-dev --optimize-autoloader

# Set permissions (Apache usually runs as www-data)
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/uploads

# Expose port 80 (Render maps to this)
EXPOSE 80
