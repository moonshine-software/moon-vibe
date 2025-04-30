FROM nginx:1.27.3-alpine AS nginx

ARG project_dir

COPY . /var/www/app
COPY ./docker/dockerfiles/nginx/config/nginx-php.conf /etc/nginx/nginx.conf

RUN sed -i "s|PROJECT_DIR|$project_dir/public|g" /etc/nginx/nginx.conf

RUN ln -s /var/www/app/storage/app/public /var/www/app/public/storage

RUN rm -rf /docker

EXPOSE 80