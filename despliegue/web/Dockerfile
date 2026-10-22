# Dockerfile - Imagen de la aplicación SGRSI (Apache + PHP).
FROM php:8.4-apache

# Extensión para que PDO se conecte a MariaDB
RUN docker-php-ext-install pdo_mysql

# Apache sirve la carpeta public, como en el servidor
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Código de la aplicación (vendor ya viene en el repositorio)
COPY PROYECTO/ /var/www/html/
COPY despliegue/web/iniciar.sh /usr/local/bin/iniciar.sh

RUN chmod +x /usr/local/bin/iniciar.sh \
    && chown -R www-data:www-data /var/www/html

CMD ["iniciar.sh"]
