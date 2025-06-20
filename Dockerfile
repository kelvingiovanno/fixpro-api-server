# Set base image (PHP with required extensions)
FROM php:8.2-cli

# Set working directory inside container
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    imagemagick \
    libmagickwand-dev \ 
    libmagickcore-dev \ 
    pkg-config \        
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/* 

# Install common PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Install and enable Imagick extension (PECL)
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Install and enable Redis extension (PECL)

RUN pecl install redis && docker-php-ext-enable redis

# Install Composer

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Laravel project files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port used by artisan serve
EXPOSE 80

# Command to run the Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]