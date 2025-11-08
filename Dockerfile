FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sshpass \
    openssh-client \
    qrencode \
    cron \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/public

# Setup cron for client expiration check (runs every hour)
RUN echo "0 * * * * www-data cd /var/www/html && /usr/local/bin/php bin/check_expired_clients.php >> /var/log/cron.log 2>&1" > /etc/cron.d/amnezia-cron \
    && chmod 0644 /etc/cron.d/amnezia-cron \
    && crontab /etc/cron.d/amnezia-cron \
    && touch /var/log/cron.log

# Create startup script
RUN echo '#!/bin/bash\n\
service cron start\n\
apache2-foreground' > /start.sh \
    && chmod +x /start.sh

# Expose port 80
EXPOSE 80

CMD ["/start.sh"]
