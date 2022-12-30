-include .env
export $(shell sed 's/=.*//' .env)

.DEFAULT_GOAL := help

PROJECT_DIR ?= /var/www/html/

dc := docker-compose -p ${APP_SLUG}
dr := $(dc) run --rm
de := $(dc) exec

.PHONY: help
help: ## Display help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: clean
build-clean: ## BUILD: Step1 - Clean docker
	@printf "$$BGreen Stop rm prune and purge all containers $$ColorOff \n"
	docker stop $$(docker ps -a -q) && docker rm $$(docker ps -a -q);\
	docker system prune --volumes --force --all && docker images purge

.PHONY: config
build-config: ## BUILD: Step2 - Create application configs
	@printf "$$BGreen Create application configs $$ColorOff \n"
	cp ./.env.example ./.env;
	@printf "$$BRed Done! $$ColorOff \n"

.PHONY: build
build-construct: ## BUILD: Step3 - Build app containers
	@printf "$$BGreen Build containers (time for a coffee break ☕ !) $$ColorOff \n"
	$(dc) up -d --build

.PHONY: build-finish
build-finish: update-project ## BUILD: Step4 - Update app

.PHONY: up
up: ## CONTAINER: Start application
	$(dc) up -d

.PHONY: down
down: ## CONTAINER: Down application
	$(dc) down

.PHONY: execute
execute: ## CONTAINER: Exec command in docker
	$(de) $(command)

.PHONY: console-php
console-php: ## CONTAINER: Go into drupal console
	$(de) php /bin/bash

.PHONY: console-web
console-web: ## CONTAINER: Go into web console
	$(de) web /bin/bash

.PHONY: console-db
console-db: ## CONTAINER: Go into db console
	$(de) db /bin/bash

.PHONY: update-project
update-project: ## DEV: Install dependencies
	@printf "$$BGreen Play composer install on project ../$(CE_APP_DIR) directory $$ColorOff \n"
	$(de) -w "$(PROJECT_DIR)" php composer -n install
