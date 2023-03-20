#!/bin/sh
composer install
composer wp-install
vendor/phpunit/phpunit/phpunit
