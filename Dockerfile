# PHP CLI image
FROM php:8.5.7-cli

# Install system packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first (better Docker cache)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# Copy application
COPY . .

# Optimize Laravel
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Expose Render port
EXPOSE 10000

# Start Laravel
CMD sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
