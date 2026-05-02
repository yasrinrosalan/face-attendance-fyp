# 1. Use PHP 8.2
FROM php:8.2-apache

# 2. Install OS dependencies and libraries needed for PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm

# 3. Configure and install the GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# 4. Install the standard Laravel PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath

# ---> NEW FIX: Increase PHP Upload Limits to 20MB <---
RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# 5. Enable Apache URL rewriting (mandatory for Laravel routing)
RUN a2enmod rewrite

# 6. Update Apache DocumentRoot to point to Laravel's 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Set working directory inside the server
WORKDIR /var/www/html

# 8. Copy all your Laravel files into the server
COPY . .

# 9. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 10. Install PHP packages
RUN composer install --no-dev --optimize-autoloader

# 11. Install Node packages and compile Tailwind/Vite frontend
RUN npm install
RUN npm run build

# 12. Fix permissions so Laravel can write to the cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 13. Expose the web port
EXPOSE 80

# 14. When the server starts, force migrations and then start Apache
CMD bash -c "php artisan migrate --force && apache2-foreground"
