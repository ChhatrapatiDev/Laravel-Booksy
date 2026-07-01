
#Base image: PHP 8.5.7
FROM php:8.5.7

#Install system dependencies
RUN apt-get update -y && apt-get install -y \ openssl zip unzip git

#Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#Install PostgreSQL client
RUN Docker-php-ext-install pdo pdo_pgsql

#Check if abstring extension is installed (for debugging purposes)
RUN php -m | grep abstring

#Set working directory
WORKDIR /app

#Copy application files to container
COPY . /app

#Install application dependencies
RUN composer install

#Command to run the Laravel development server
CMD php artisan serve --host=0.0.0.0 --port=8000

#Expose port 8000
EXPOSE 8000
