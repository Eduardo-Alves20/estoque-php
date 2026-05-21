FROM php:8.2-apache

# Instala dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    nodejs \
    npm \
    && docker-php-ext-install mbstring zip gd pdo_mysql mysqli

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia apenas os arquivos necessários para dependências
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Corrige erro do Git sobre permissões no diretório
RUN git config --global --add safe.directory /var/www/html

# Copia Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permite rodar Composer como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala dependências PHP e Node.js
RUN composer install --no-interaction
RUN npm install

# Por fim, copia todo o restante do projeto (evita sobrescrever vendor prematuramente)
COPY . .
