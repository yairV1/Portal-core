FROM php:8.2-apache

# Extensiones PHP que el proyecto realmente usa:
# - pdo_mysql: config/database.php (conexión a MySQL)
# - fileinfo: ya viene habilitada por defecto en esta imagen (finfo, usada
#   en TrabajoController.php/ContratacionController.php/DocumentoController.php
#   para validar el contenido real de PDFs/imágenes, no la extensión)
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Todo pasa por public/index.php (ver public/.htaccess) — el DocumentRoot
# de Apache tiene que apuntar ahí, no a la raíz del proyecto.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Composer corre DENTRO de la imagen — no depende del vendor/ que ya tengas
# en tu máquina, así el build es reproducible en cualquier equipo/servidor.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

COPY . .

# Carpetas de almacenamiento (hojas de vida, documentos de contratación,
# fotos de perfil/logo) — deben existir y ser escribibles por Apache.
# Los datos reales viven en volúmenes (ver docker-compose.yml), esto solo
# asegura que las carpetas existan con los permisos correctos.
RUN mkdir -p storage/postulaciones storage/contrataciones storage/documentos public/uploads \
    && chown -R www-data:www-data storage public/uploads \
    && chmod -R 775 storage public/uploads

EXPOSE 80
