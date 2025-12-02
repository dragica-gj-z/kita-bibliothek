FROM php:8.2-apache

# Systempakete & PHP-Extensions
# - curl/gnupg/ca-certificates: für Nodesource GPG-Setup
# - netcat-openbsd: für DB-Wait im entrypoint
# - build-essential + python3: häufig benötigt für node-gyp (native npm-Module)
RUN apt-get update && apt-get install -y \
    ca-certificates curl gnupg \
    libzip-dev zip unzip git nano netcat-openbsd \
    build-essential python3 \
 && docker-php-ext-install pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Node.js 20 + npm (Nodesource)
RUN mkdir -p /etc/apt/keyrings \
 && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
 | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
 && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" \
 > /etc/apt/sources.list.d/nodesource.list \
 && apt-get update && apt-get install -y nodejs \
 && node -v && npm -v
# Apache Rewrite
RUN a2enmod rewrite

# DocumentRoot auf /public umstellen und <Directory>-Block ergänzen
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
 && printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    Options Indexes FollowSymLinks' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' >> /etc/apache2/apache2.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Rechte (optional – meist durch Bind-Mount egal)
RUN chown -R www-data:www-data /var/www/html
RUN git config --global --add safe.directory /var/www/html

# Flags für Asset-Build im entrypoint
ENV BUILD_ASSETS=true \
    NODE_ENV=production \
    CI=true

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]  