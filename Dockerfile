FROM php:8.2-apache

# Systempakete & PHP-Extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl nano netcat-openbsd \
 && docker-php-ext-install pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Apache Rewrite aktivieren
RUN a2enmod rewrite

# DocumentRoot auf /public umstellen und passenden <Directory>-Block ergänzen
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
 && printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    Options Indexes FollowSymLinks' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' >> /etc/apache2/apache2.conf

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis
WORKDIR /var/www/html

# (Optional) App-Code in Image kopieren – auskommentieren, falls du bind-mounts benutzt
# COPY . .

# Rechte
RUN chown -R www-data:www-data /var/www/html

# Start-Skript (führt Composer & Migrationen aus, wartet auf DB)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# OPTIONAL: Build-Arg, um Migrationen auch beim Build auszuführen (nicht empfohlen)
ARG RUN_MIGRATIONS=false
ENV RUN_MIGRATIONS=${RUN_MIGRATIONS}

# Falls du zur Build-Zeit migrieren *willst* und DB erreichbar ist:
# RUN if [ "$RUN_MIGRATIONS" = "true" ] && [ -f artisan ]; then \
#       composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader && \
#       php artisan migrate --force --no-interaction; \
#     fi

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
