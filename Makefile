# If the first argument is "composer"...
ifeq (composer,$(firstword $(MAKECMDGOALS)))
  # use the rest as arguments for "composer"
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  # ...and turn them into do-nothing targets
  $(eval $(RUN_ARGS):;@:)
endif

.PHONY: ci test phpunit cs covers composer

DEFAULT_GOAL := ci

ci: test cs

travis: covers travis-unit cs

travis-unit:
	docker-compose run --rm app ./vendor/bin/phpunit --printer="PHPUnit\TextUI\ResultPrinter"

test: covers phpunit

cs: phpcs

phpunit:
	docker-compose run --rm app ./vendor/bin/phpunit

phpcs:
	docker-compose run --rm app ./vendor/bin/phpcs -p -s

covers:
	docker-compose run --rm app ./vendor/bin/covers-validator

composer:
	docker run --rm --interactive --tty --volume $(shell pwd):/app -w /app\
	 --volume ~/.composer:/composer --user $(shell id -u):$(shell id -g) composer composer --no-scripts $(filter-out $@,$(MAKECMDGOALS))
