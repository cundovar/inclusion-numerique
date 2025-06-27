FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip intl gd opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www/symfony

# 📦 Étape 1 : copier uniquement les fichiers nécessaires pour composer
COPY composer.json composer.lock ./


# 📂 Étape 3 : copier le reste du projet
COPY . .

# 🔐 Fixer les permissions
RUN chown -R www-data:www-data /var/www/symfony

# Exposer port (utile pour documentation)
EXPOSE 9000

# Lancer PHP-FPM
CMD ["php-fpm"]
