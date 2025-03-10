FROM mysql:9.2.0

ARG db_root_pass

COPY ./docker/dockerfiles/mysql/config/create_database.sql /docker-entrypoint-initdb.d/create_database.sql
RUN sed -i "s/MYSQL_ROOT_PASSWORD/$db_root_pass/g" /docker-entrypoint-initdb.d/create_database.sql

COPY ./docker/dockerfiles/mysql/config/mysql.cnf /etc/mysql/conf.d/mysql.cnf

RUN rm -rf /docker