UID=$(shell id -u)
GID=$(shell id -g)
FILE=docker-compose.yml

.PHONY: bash
bash: ## gets inside a php container
	UID=${UID} GID={GID} docker compose -f ${FILE} exec --user=${UID} php sh

.PHONY: build
build: ## docker-compose build
	UID=${UID} GID={GID} docker compose -f ${FILE} build

.PHONY: up
up: ## up all containers
	UID=${UID} GID=${GID} docker compose -f ${FILE} up -d

.PHONY: stop
stop: ## stop all containers
	UID=${UID} GID=${GID} docker compose -f ${FILE} stop

.PHONY: down
down: ## down all containers
	UID=${UID} GID=${GID} docker compose -f ${FILE} down

.PHONY: check
check: ## check games command, t=false to disable telegram notifications
	UID=${UID} GID=${GID} docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console new-game-notifier:check -t $(t)"

.PHONY: install
install: ## composer install for php container
	UID=${UID} GID=${GID} docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/composer.phar install"

.PHONY: init
init: ## run migrations
	UID=${UID} GID=${GID} docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console environment:init"

.PHONY: tests
tests: ## execute project unit tests
	docker compose -f ${FILE} exec --user=${UID} php sh -c "phpunit --order=random"

.PHONY: stan
stan: ## pass phpstan
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php -d memory_limit=256M vendor/bin/phpstan analyse -c phpstan.neon"

.PHONY: cs
cs: ## run phpcs checker
	docker compose -f ${FILE} exec --user=${UID} php sh -c "phpcs --standard=phpcs.xml.dist"

.PHONY: ps
ps: ## status from all containers
	docker compose -f ${FILE} ps

import-games:
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console steam:import:games"

import-everything:
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console steam:import:games -rc"

import-games-recent:
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console steam:import:games-recent"

import-reviews:
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console steam:import:reviews"

import-completions:
	docker compose -f ${FILE} exec --user=${UID} php sh -c "php bin/console steam:import:completions"
