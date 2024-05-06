#!/bin/sh

set -e

if [ "$1" = "-dev" ]; then
    echo "\n\e[33mНАЧИНАЕМ ДЕПЛОЙ ИЗ ВЕТКИ DEV \e[0m\n"
    git checkout dev
    git pull
else
    echo "\n\e[33mНАЧИНАЕМ ДЕПЛОЙ ИЗ ВЕТКИ MASTER \e[0m\n"
    git checkout master
    git pull
fi

echo "\n\e[33mУСТАНАВЛИВАЕМ ЗАВИСИМОСТИ \e[0m\n"
composer install --no-dev --optimize-autoloader

if [ "$1" = "-dev" ]; then
    echo "\n\e[33mНАКАТЫВАЕМ МИГРАЦИИ И ГЕНЕРИРУЕМ КОНТЕНТ \e[0m\n"
    php artisan migrate:fresh --seed
fi

echo "\n\e[33mКЕШИРУЕМ ФАЙЛЫ \e[0m\n"
php artisan optimize

echo "\n\e[33mДЕПЛОЙ УСПЕШНО ЗАВЕРШЕН \e[0m\n"
