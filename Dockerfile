FROM php:8.2-apache

# Install system dependencies for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p backend/uploads/{highlights,icons,logos,photos,posters} \
    && mkdir -p backend/logs \
    && chmod -R 755 backend/uploads \
    && chmod -R 755 backend/logs \
    && chown -R www-data:www-data /var/www/html

# Debug: Check file structure and permissions
RUN ls -la /var/www/html/ && \
    ls -la /var/www/html/backend/ && \
    echo "File structure verified"

# Create a proper Apache configuration file
RUN echo 'ServerName localhost\n\
<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/backend\n\
    <Directory /var/www/html/backend>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php index.html\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Enable the site
RUN a2ensite 000-default

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
