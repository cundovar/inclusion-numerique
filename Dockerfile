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

# Moteur de capture automatique (capture/) : Node.js et Chromium de Debian.
# Playwright n'a pas besoin de télécharger son propre navigateur : CAPTURE_CHROMIUM_PATH pointe sur celui-ci.
RUN apt-get update && apt-get install -y --no-install-recommends nodejs npm chromium fonts-dejavu-core \
    && rm -rf /var/lib/apt/lists/*
ENV CAPTURE_CHROMIUM_PATH=/usr/bin/chromium \
    PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www/symfony

# Copy custom PHP-FPM configuration
COPY php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# 📦 Étape 1 : copier uniquement les fichiers nécessaires pour composer
COPY composer.json composer.lock ./


# 📂 Étape 3 : copier le reste du projet
COPY . .

# Dépendances du moteur de capture
RUN npm ci --prefix capture --omit=dev

# 🔐 Fixer les permissions
RUN chown -R www-data:www-data /var/www/symfony

# Exposer port (utile pour documentation)
EXPOSE 9000

# Lancer PHP-FPM
CMD ["php-fpm"]
