# Usa PHP con Apache
FROM php:7.4-apache

# Instala dependencias necesarias del sistema para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copia el código PHP al contenedor
COPY /tfg /var/www/html/

# Da permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Activa módulos de Apache
RUN a2enmod rewrite

EXPOSE 80
