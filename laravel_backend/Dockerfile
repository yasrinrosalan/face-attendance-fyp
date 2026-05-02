# Use PHP 8.2 with an Apache web server
FROM php:8.2-apache

# Install OS dependencies, Node.js, and PostgreSQL drivers
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm

# Install PHP extensions required for Laravel and PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache URL rewriting (mandatory for Laravel routing)
RUN a2enmod rewrite

# Update Apache DocumentRoot to point to Laravel's 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory inside the server
WORKDIR /var/www/html

# Copy all your Laravel files into the server
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP packages
RUN composer install --no-dev --optimize-autoloader

# Install Node packages and compile Tailwind/Vite frontend
RUN npm install
RUN npm run build

# Fix permissions so Laravel can write to the cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the web port
EXPOSE 80

# When the server starts, force migrations and then start Apache
CMD bash -c "php artisan migrate --force && apache2-foreground"
