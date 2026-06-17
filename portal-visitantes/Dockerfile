# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

RUN apt-get update && apt-get upgrade -y

# Instala as dependências do sistema operacional
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Configura e instala as extensões LDAP e cURL no PHP
RUN docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ \
    && docker-php-ext-install ldap curl

# Habilita o módulo de reescrita de URL do Apache (opcional, mas recomendado)
RUN a2enmod rewrite

# Ajusta as permissões da pasta web
RUN chown -R www-data:www-data /var/www/html
