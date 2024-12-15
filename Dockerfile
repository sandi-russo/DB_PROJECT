FROM php:7.4-apache

# Installa le dipendenze necessarie e l'estensione mysqli
RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

# Opzionale: installa altre estensioni PHP utili
RUN docker-php-ext-install pdo pdo_mysql