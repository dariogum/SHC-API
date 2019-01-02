@echo off
D:
cd shc-api
git pull
php -S localhost:8080 -t public public/index.php
PAUSE