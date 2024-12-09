# Use the official PHP image with version 8.3
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    zip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy plugin files to the container
COPY . /var/www/html

# Install Composer dependencies
RUN composer packages-install

# Run tests
CMD ["composer", "test"]
