#Utiliza php con apache
FROM php:7.4-apache

#Copia el contenido del directorio actual al directorio raíz del contenedor
COPY . /var/www/html/

#Ejecuta el comando de instalación de dependencias
RUN apt-get update && apt-get install -y \
    php-curl \
    php-gd \
    php-mbstring \
    php-xml \
    php-zip \
    php-json \
    php-mysql \
    php-pdo \
    php-opcache \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

#Instalar postgresql
RUN apt install -y postgresql postgresql-contrib
