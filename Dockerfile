FROM php:8.2-apache

# Extensão necessária para PDO MySQL (usada em app/Config/Database.php)
RUN docker-php-ext-install pdo pdo_mysql

# Extensões necessárias para o PHPWord gerar o contrato em Word (.docx)
# libzip-dev precisa vir antes, senão o "docker-php-ext-install zip" falha no build
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer, para instalar a biblioteca phpoffice/phpword (contrato em Word)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Aponta o DocumentRoot do Apache para a pasta /public do projeto
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!\${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!\${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

COPY . /var/www/html

# Instala as dependências PHP (biblioteca phpoffice/phpword)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Garante que a aplicação consiga gravar exames enviados (storage/exams)
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# O Render define a porta em tempo de execução via $PORT (padrão 10000).
# Ajustamos o Apache para escutar nela no início do container.
RUN printf '#!/bin/sh\nset -e\nPORT="${PORT:-10000}"\nsed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf\nsed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/start-apache.sh \
    && chmod +x /usr/local/bin/start-apache.sh

EXPOSE 10000
CMD ["/usr/local/bin/start-apache.sh"]
