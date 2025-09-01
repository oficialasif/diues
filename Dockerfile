FROM php:8.2-apache

# Install system dependencies for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache Rewrite Module
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy project files
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p backend/uploads/{highlights,icons,logos,photos,posters} \
    && mkdir -p backend/logs \
    && chmod -R 755 backend/uploads \
    && chmod -R 755 backend/logs \
    && chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
