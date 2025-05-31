# Usa PHP con Apache
FROM php:7.4-apache

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configura y instala extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    mysqli \
    pdo_mysql

# Instala Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuración PHP personalizada
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Habilita módulos Apache necesarios
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod ssl

# Configura Apache
COPY apache/apache2.conf /etc/apache2/apache2.conf
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Crea directorios necesarios
RUN mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/cache

# Copia el código PHP al contenedor
COPY php/ /var/www/html/

# Copia archivos de configuración
COPY config/ /var/www/html/config/

# Instala dependencias de Composer si existe composer.json
RUN if [ -f /var/www/html/composer.json ]; then \
    cd /var/www/html && composer install --no-dev --optimize-autoloader; \
    fi

# Establece los permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/cache

# Configura el usuario para que no sea root
USER www-data

EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Comando por defecto
CMD ["apache2-foreground"]