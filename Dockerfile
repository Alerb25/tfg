#Utiliza php con apache
FROM php:7.4-apache

#Copia el contenido del directorio actual al directorio raíz del contenedor
COPY /php /var/www/html/

# Instala extensiones necesarias de PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Da permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Activa módulos de Apache
RUN a2enmod rewrite

EXPOSE 80
