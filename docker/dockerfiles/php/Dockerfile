FROM php:8.4.4-fpm AS php

WORKDIR /var/www/app

ARG user
ARG uid

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libc6 \
    zip \
    unzip \
    supervisor \
    htop \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    default-mysql-client

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN pecl install redis

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

FROM php AS dev

COPY ./docker/dockerfiles/php/config/php-dev.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/dockerfiles/php/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./docker/dockerfiles/php/config/php-fpm-dev.conf /usr/local/etc/php-fpm-dev.d/www.conf

ARG xdebug_idekey
ARG xdebug_client_port

RUN pecl install xdebug-3.4.1 && \
      docker-php-ext-enable xdebug && \
      sed -i "s/^xdebug\.idekey=.*/xdebug.idekey=${xdebug_idekey}/" /usr/local/etc/php/conf.d/xdebug.ini && \
      sed -i "s/^xdebug\.client_port=.*/xdebug.client_port=${xdebug_client_port}/" /usr/local/etc/php/conf.d/xdebug.ini

COPY . .

RUN chmod -R 775 ./storage ./bootstrap/cache

COPY ./docker/dockerfiles/php/config/entrypoint-dev.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

USER $user

FROM php AS build

COPY ./docker/dockerfiles/php/config/php-prod.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/dockerfiles/php/config/php-fpm-prod.conf /usr/local/etc/php-fpm.d/www.conf

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --no-interaction

COPY . .

RUN composer dump-autoload --optimize && \
    composer check-platform-reqs

RUN chmod -R 775 ./storage ./bootstrap/cache

FROM build AS prod
COPY ./docker/dockerfiles/php/config/entrypoint-prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN rm -rf /docker
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM build AS worker
COPY ./docker/dockerfiles/php/config/supervisord.conf /etc/supervisor/supervisord.conf
RUN rm -rf /docker
CMD ["/bin/sh", "-c", "supervisord -c /etc/supervisor/supervisord.conf"]

FROM build AS scheduler
RUN rm -rf /docker
CMD ["/bin/sh", "-c", "nice -n 10 sleep 60 && php /var/www/app/artisan schedule:run --verbose --no-interaction"]