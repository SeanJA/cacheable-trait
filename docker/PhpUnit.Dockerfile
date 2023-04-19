FROM php:8.2-cli

RUN apt-get update && apt-get -y install \
    libzip-dev \
    git \
    unzip \
    curl

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV XDEBUG_MODE=coverage

# run each thing separately so that we know which one failed
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

WORKDIR /var/www/html

COPY ./docker/phpunit-setup.sh /usr/local/bin/phpunit-setup


RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

RUN chmod +x /usr/local/bin/phpunit-setup && \
    chmod 755 /usr/local/bin/phpunit-setup

ENTRYPOINT ["phpunit-setup"]