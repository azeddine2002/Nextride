FROM php:8.2-apache

# Installation des dépendances système et extensions PHP requises par Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && a2enmod rewrite

# Installation de Composer (gestionnaire de dépendances PHP) dans l'image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# On copie tout le code du projet dans le conteneur
COPY . .

# On installe les dépendances PHP du projet (celles listées dans composer.json)
RUN composer install --no-interaction --optimize-autoloader

# var/ (cache, logs) doit rester ecrivable par Apache (www-data), notamment quand ce dossier
# est monte comme volume Docker dedie plutot que via le bind mount du code source
RUN chown -R www-data:www-data var/

# Symfony sert son site depuis le dossier /public, donc on dit à Apache de pointer là-dessus
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
EXPOSE 80