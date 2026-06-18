# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# 1. Diz ao sistema para não pedir confirmações (evita bloqueios interativos)
ENV DEBIAN_FRONTEND=noninteractive

# 2. Força o uso de IPv4 (corrige problemas de rede no Docker for Windows)
# 3. Não instala pacotes recomendados desnecessários
RUN apt-get update -o Acquire::ForceIPv4=true \
    && apt-get install -y -o Acquire::ForceIPv4=true --no-install-recommends \
    libldap2-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Configura e instala as extensões LDAP e cURL no PHP
RUN docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ \
    && docker-php-ext-install ldap curl

# Habilita o módulo de reescrita de URL do Apache
RUN a2enmod rewrite

# Ajusta as permissões da pasta web
RUN chown -R www-data:www-data /var/www/html