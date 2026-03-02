FROM php:8.2-apache

# Install PHP extensions needed for the app
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix MPM conflict: disable mpm_event, keep only mpm_prefork
RUN a2dismod mpm_event 2>/dev/null; a2enmod mpm_prefork

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache to use Railway's PORT env variable at runtime
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf
RUN sed -i 's/*:80/*:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Default port
ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]
