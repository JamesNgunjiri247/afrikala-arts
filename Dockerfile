# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Copy all files in your repo to the web server's root directory
COPY . /var/www/html/

# Set permissions for uploads (if you need file uploads)
RUN chown -R www-data:www-data /var/www/html/uploads

# Enable Apache mod_rewrite (optional, but often needed)
RUN a2enmod rewrite

# Expose port 80 (the default web server port)
EXPOSE 80