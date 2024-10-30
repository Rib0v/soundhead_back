FROM php:8.3.6-fpm-alpine

WORKDIR /var/www/laravel

RUN apk add linux-headers postgresql-dev autoconf g++ make \
    && pecl install redis \
    && pecl install xdebug \
    && docker-php-ext-install pdo_pgsql pgsql \
    && docker-php-ext-enable redis.so xdebug && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*