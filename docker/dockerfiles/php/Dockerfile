FROM php:8.4.7-fpm AS php

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

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2.8.9 /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user

RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

RUN mkdir -p /root/.composer
RUN cp /home/$user/.composer/auth.json /root/.composer/auth.json

FROM php AS dev

ARG xdebug_idekey
ARG xdebug_client_port

COPY ./docker/config/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN pecl install xdebug-3.4.4 && \
      docker-php-ext-enable xdebug && \
      sed -i "s/^xdebug\.idekey=.*/xdebug.idekey=${xdebug_idekey}/" /usr/local/etc/php/conf.d/xdebug.ini && \
      sed -i "s/^xdebug\.client_port=.*/xdebug.client_port=${xdebug_client_port}/" /usr/local/etc/php/conf.d/xdebug.ini

COPY . .

RUN chmod -R 775 ./bootstrap/cache && chmod g+w /var/www/app/storage && chmod g+s /var/www/app/storage

COPY ./docker/config/php/entrypoint-dev.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

USER $user

FROM php AS build

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --no-interaction

COPY . .

RUN composer dump-autoload --optimize && \
    composer check-platform-reqs

RUN chmod -R 775 ./bootstrap/cache && chmod g+w /var/www/app/storage && chmod g+s /var/www/app/storage

FROM build AS prod
COPY ./docker/config/php/entrypoint-prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN rm -rf /docker
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

FROM build AS worker
RUN rm -rf /docker
CMD ["/bin/sh", "-c", "supervisord -c /etc/supervisor/supervisord.conf"]

FROM build AS scheduler
RUN rm -rf /docker
CMD ["/bin/sh", "-c", "nice -n 10 sleep 60 && php /var/www/app/artisan schedule:run --verbose --no-interaction"]