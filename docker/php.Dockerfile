FROM php:8.3.6-fpm-alpine

WORKDIR /var/www/laravel

RUN apk add postgresql-dev autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-install pdo_pgsql pgsql \
    && docker-php-ext-enable redis.so && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*