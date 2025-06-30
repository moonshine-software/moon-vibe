-include .env

THIS_FILE := $(lastword $(MAKEFILE_LIST))

app := $(COMPOSE_PROJECT_NAME)-php
nginx := $(COMPOSE_PROJECT_NAME)-nginx
mysql := $(COMPOSE_PROJECT_NAME)-mysql
app-npm := npm
path := /var/www/app

#docker
.PHONY: build
build:
	docker-compose -f docker-compose.yml up --build -d $(c)
	@echo "Run command: make install"
	@echo "$(APP_URL)"

.PHONY: install
install: composer-install composer-update migrate-fresh npm-install npm-update npm-build restart-worker
	@echo "$(APP_URL)"

.PHONY: rebuild
rebuild:
	docker-compose up -d --force-recreate --no-deps --build $(r)

.PHONY: rebuild-app
rebuild-app:
	docker-compose up -d --force-recreate --no-deps --build php

.PHONY: up
up:
	docker-compose -f docker-compose.yml up -d $(c)
	@echo "$(APP_URL)"

.PHONY: stop
stop:
	docker-compose -f docker-compose.yml stop $(c)

.PHONY: it
it:
	docker exec -it $(to) /bin/bash

.PHONY: it-app
it-app:
	docker exec -it $(app) /bin/bash

.PHONY: it-nginx
it-nginx:
	docker exec -it $(nginx) /bin/bash

.PHONY: it-mysql
it-mysql:
	docker exec -it $(mysql) /bin/bash

.PHONY: restart-worker
restart-worker:
	docker restart $(COMPOSE_PROJECT_NAME)-worker

.PHONY: migrate
migrate:
	docker exec $(app) php $(path)/artisan migrate

.PHONY: migrate-rollback
migrate-rollback:
	docker exec $(app) php $(path)/artisan migrate:rollback

.PHONY: migrate-fresh
migrate-fresh:
	docker exec $(app) php $(path)/artisan migrate:fresh --seed

.PHONY: migration
migration:
	docker exec $(app) php $(path)/artisan make:migration $(m)

#composer
.PHONY: composer-install
composer-install:
	docker exec $(app) composer install

.PHONY: composer-update
composer-update:
	docker exec $(app) composer update

.PHONY: composer-du
composer-du:
	docker exec $(app) composer du

.PHONY: test
test:
	docker exec $(app) composer test

.PHONY: analyse
analyse:
	docker exec $(app) composer analyse

#npm
.PHONY: npm
npm:
	docker-compose run --rm --service-ports $(app-npm) $(c)

.PHONY: npm-install
npm-install:
	docker-compose run --rm --service-ports $(app-npm) install $(c)

.PHONY: npm-update
npm-update:
	docker-compose run --rm --service-ports $(app-npm) update $(c)

.PHONY: npm-build
npm-build:
	docker-compose run --rm --service-ports $(app-npm) run build $(c)

.PHONY: npm-host
npm-host:
	docker-compose run --rm --service-ports $(app-npm) run dev --host $(c)

.PHONY: deploy
deploy:
	git tag $(t) && git push origin $(t)