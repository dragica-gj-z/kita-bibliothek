############################
# 1. Frontend-Build (Vite) #
############################
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json vite.config.* ./
COPY resources ./resources
COPY public ./public

RUN npm install
RUN npm run build


############################
# 2. PHP + Apache / Laravel #
############################
FROM php:8.2-apache

# Systempakete und PHP-Extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*


# Arbeitsverzeichnis
WORKDIR /var/www/html

# Composer aus offiziellem Image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Restliche App
COPY . .

# Dependencies installieren
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Gebaute Frontend-Assets aus dem Node-Stage
COPY --from=frontend /app/public/build ./public/build

# Rechte für Laravel (Verzeichnisse sicherstellen & Rechte setzen)
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache


# DocumentRoot auf /public setzen und Laravel-Directory konfigurieren
RUN sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Entrypoint-Script kopieren
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
