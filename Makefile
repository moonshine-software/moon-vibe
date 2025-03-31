-include .env

THIS_FILE := $(lastword $(MAKEFILE_LIST))

app := $(COMPOSE_PROJECT_NAME)-php
nginx := $(COMPOSE_PROJECT_NAME)-nginx
mysql := $(COMPOSE_PROJECT_NAME)-mysql
app-npm := npm
path := /var/www/app

#docker
build:
	docker-compose -f docker-compose.yml up --build -d $(c)
	@echo "Run command: make install"
	@echo "$(APP_URL)"
install: composer-install composer-update migrate npm-install npm-update npm-build test
	@echo "$(APP_URL)"
rebuild:
	docker-compose up -d --force-recreate --no-deps --build $(r)
rebuild-app:
	docker-compose up -d --force-recreate --no-deps --build php
up:
	docker-compose -f docker-compose.yml up -d $(c)
	@echo "$(APP_URL)"
stop:
	docker-compose -f docker-compose.yml stop $(c)
it:
	docker exec -it $(to) /bin/bash
it-app:
	docker exec -it $(app) /bin/bash
it-nginx:
	docker exec -it $(nginx) /bin/bash
it-mysql:
	docker exec -it $(mysql) /bin/bash

migrate:
	docker exec $(app) php $(path)/artisan migrate
migrate-rollback:
	docker exec $(app) php $(path)/artisan migrate:rollback
migrate-fresh:
	docker exec $(app) php $(path)/artisan migrate:fresh --seed
migration:
	docker exec $(app) php $(path)/artisan make:migration $(m)

#composer
composer-install:
	docker exec $(app) composer install
composer-update:
	docker exec $(app) composer update
composer-du:
	docker exec $(app) composer du
test:
	docker exec $(app) composer test
analyse:
	docker exec $(app) composer analyse

#npm
npm:
	docker-compose run --rm --service-ports $(app-npm) $(c)
npm-install:
	docker-compose run --rm --service-ports $(app-npm) install $(c)
npm-update:
	docker-compose run --rm --service-ports $(app-npm) update $(c)
npm-build:
	docker-compose run --rm --service-ports $(app-npm) run build $(c)
npm-host:
	docker-compose run --rm --service-ports $(app-npm) run dev --host $(c)

#app
admin:
	tar -xvf storage/app/private/builds/1/app/project.tar.gz -C "storage" && chown -R $(DOCKER_USER):$(DOCKER_USER) storage/project && cd storage/project && cp .env.example .env && make build && make install
clear: clear-dir
clear-dir:
	sudo rm -rf storage/project
