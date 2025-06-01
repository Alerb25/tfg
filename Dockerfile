# Dockerfile para Apache + PHP - Sistema de Gestión de Notas
FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilitar módulos de Apache necesarios
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod ssl


# Configurar PHP
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Configurar Apache para el proyecto
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar DirectoryIndex para que busque index.php primero
RUN echo "DirectoryIndex login.php " >> /etc/apache2/apache2.conf
# Configurar directorio
RUN echo '<Directory /var/www/html/>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</Directory>' >> /etc/apache2/sites-available/000-default.conf


# Crear directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto (excluir algunos archivos innecesarios)
COPY --chown=www-data:www-data . /var/www/html/

# Establecer permisos adecuados
RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod +x /var/www/html/*.php

# Crear directorio para logs si no existe
RUN mkdir -p /var/www/html/logs && chown www-data:www-data /var/www/html/logs

# Exponer puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]