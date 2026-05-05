start:
	docker-compose up -d

stop:
	docker-compose down

build:
	docker-compose build

rebuild:
	docker-compose down
	docker-compose up -d --build

shell:
	docker-compose exec app bash

migrate:
	docker-compose exec app php artisan migrate

stan:
	docker-compose exec app ./vendor/bin/phpstan analyse --memory-limit=2G

format:
	docker-compose exec app ./vendor/bin/pint