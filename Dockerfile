# Use official PHP image with Apache
FROM php:8.3-apache

# Enable Apache mod_rewrite (optional, but common)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# (No COPY step; code will be mounted as a volume for development)

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
