FROM nginx:1.29-alpine  AS nginx

COPY . /var/www/app

RUN ln -s /var/www/app/storage/app/public /var/www/app/public/storage

RUN rm -rf /docker

EXPOSE 80