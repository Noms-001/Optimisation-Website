FROM php:8.2-apache

# Extensions nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Activer rewrite
RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install gd

# Copier config apache
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Permissions (important pour uploads)
RUN chown -R www-data:www-data /var/www/html