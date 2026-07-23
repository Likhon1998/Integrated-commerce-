FROM php:8.2-cli

# Install system dependencies and PostgreSQL extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer into the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory to the root of your application
WORKDIR /app

# Copy the application code (Docker will automatically skip files listed in .dockerignore)
COPY . .

# Install Laravel production dependencies without development packages
RUN composer install --no-dev --optimize-autoloader

# Run the startup script and serve the application using Render's assigned dynamic port
CMD bash start.sh && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
