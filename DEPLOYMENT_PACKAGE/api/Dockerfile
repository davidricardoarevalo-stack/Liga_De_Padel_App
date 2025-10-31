FROM composer:2 AS builder
WORKDIR /app

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* /app/  
# Copy the rest of the application
COPY . /app

# Install PHP dependencies (no dev) in the builder stage
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

FROM php:8.2-apache
RUN apt-get update \
  && apt-get install -y --no-install-recommends libzip-dev unzip git zip \
  && docker-php-ext-install pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=builder /app /var/www/html
RUN chown -R www-data:www-data /var/www/html \
  && a2enmod rewrite

# Configure Apache to serve from public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
