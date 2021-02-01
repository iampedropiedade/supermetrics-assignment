build:
	docker-compose build

start:
	docker-compose up -d

stop:
	docker stop php_app

fix:
	docker run --entrypoint "" --rm -it -v $(CURDIR)/app:/var/www/html -v $(CURDIR)/phpcs.xml:/var/www/html/phpcs.xml php:7.4-apache bash -c "cd /var/www/html && php vendor/bin/phpcbf"

stan:
	docker run --entrypoint "" --rm -it -v $(CURDIR)/phpstan.neon:/var/www/html/phpstan.neon -v $(CURDIR)/app:/var/www/html php:7.4-apache bash -c "cd /var/www/html && php vendor/bin/phpstan analyse"

phpcs:
	docker run --entrypoint "" --rm -it -v $(CURDIR)/phpcs.xml:/var/www/html/phpcs.xml -v $(CURDIR)/app:/var/www/html php:7.4-apache bash -c "cd /var/www/html && php vendor/bin/phpcs"

