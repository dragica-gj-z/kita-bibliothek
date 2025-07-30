FROM php:8.2-apache

# System & PHP-Erweiterungen installieren
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip

# Apache Rewrite aktivieren
RUN a2enmod rewrite

# Setze DocumentRoot direkt in Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>' >> /etc/apache2/apache2.conf

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# Rechte (optional)
RUN chown -R www-data:www-data /var/www/html

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
