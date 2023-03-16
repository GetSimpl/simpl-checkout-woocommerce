#!/bin/sh
set -eu
export PATH="$(pwd)/vendor/bin:$PATH"
composer install
phpunit
