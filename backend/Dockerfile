FROM php:8.1-apache

# Install required PHP extensions and supervisor
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl vim \
    libicu-dev libpq-dev libc-client-dev libkrb5-dev supervisor \
    && docker-php-ext-install zip pdo pdo_mysql

RUN docker-php-ext-install zip pdo pdo_mysql pcntl 

# Install the imap extension
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap

# Install the Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Setup xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Enable mod_rewrite for Laravel
RUN a2enmod rewrite

# Install composer
COPY --from=composer:2.3 /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the application code
COPY . .

# Adding virtual host
WORKDIR /etc/apache2/sites-available
COPY 000-default.conf /etc/apache2/sites-available/

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod 755 /var/www/html/storage -R

# Copy the supervisor config from the host
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY .env.example /var/www/html/backend/.env

# Install Laravel dependencies
WORKDIR /var/www/html
RUN composer install
RUN composer dumpautoload

# Clear caches
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear 
    
# Run migrations
RUN php artisan migrate; exit 0
RUN php artisan elastic:migrate; exit 0

# Expose the port
EXPOSE 80

# Start supervisor and apache in background
CMD service supervisor start && service apache2 start && tail -f /dev/null
