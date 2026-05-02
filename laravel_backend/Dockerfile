# 1. Use PHP 8.2 (Change this to 8.1 or 8.3 if your local Laravel needs it!)
FROM php:8.2-apache

# 2. Install OS dependencies and libraries needed for PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm

# 3. Install the standard Laravel PHP extensions (added zip and bcmath!)
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath

# 4. Enable Apache URL rewriting (mandatory for Laravel routing)
RUN a2enmod rewrite

# 5. Update Apache DocumentRoot to point to Laravel's 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Set working directory inside the server
WORKDIR /var/www/html

# 7. Copy all your Laravel files into the server
COPY . .

# 8. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 9. Install PHP packages
# (If it STILL fails, change this line to: RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs)
RUN composer install --no-dev --optimize-autoloader

# 10. Install Node packages and compile Tailwind/Vite frontend
RUN npm install
RUN npm run build

# 11. Fix permissions so Laravel can write to the cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Expose the web port
EXPOSE 80

# 13. When the server starts, force migrations and then start Apache
CMD bash -c "php artisan migrate --force && apache2-foreground"
