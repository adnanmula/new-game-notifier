FROM php:8.2-fpm-alpine

RUN apk update && apk add --no-cache \
    oniguruma-dev \
    postgresql-dev \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        mbstring \
        pdo \
        pdo_pgsql

RUN mkdir /.composer \
    && chown -R www-data:www-data /.composer

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/bin --filename=composer && chmod +x /usr/bin/composer

ENV PATH /var/www/html/bin:/var/www/html/vendor/bin:$PATH
