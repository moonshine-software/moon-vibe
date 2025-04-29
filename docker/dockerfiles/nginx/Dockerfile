FROM node:23.11.0-alpine3.21 AS base
WORKDIR /var/www/app
COPY . /var/www/app
RUN npm install

FROM base AS build
RUN npm run build

FROM nginx:1.27.3-alpine AS web
ARG project_dir
COPY . /var/www/app
COPY ./docker/dockerfiles/nginx/config/nginx-php.conf /etc/nginx/nginx.conf
RUN sed -i "s|PROJECT_DIR|$project_dir/public|g" /etc/nginx/nginx.conf
RUN rm -rf /docker

FROM web AS dev
EXPOSE 80

FROM web AS prod
COPY --from=build /var/www/app/public/build /var/www/app/public/build
EXPOSE 80