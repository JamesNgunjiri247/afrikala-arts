FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy all files to the Apache server root
COPY . /var/www/html/

# Set permissions for uploads (optional, if you use uploads)
RUN chown -R www-data:www-data /var/www/html/uploads || true

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

EXPOSE 80