#!/bin/bash

case "$1" in

  init)
    echo "Initializing..."
    isEnvCreatedFirst=0

    if [ ! -f .env ]; then # если файл .env не существует
      isEnvCreatedFirst=1
      cp .env.doc.example .env # копируем образец конфига
      echo -e "\nDOCKER_USER=$(id -u):$(id -g)" >> .env # записываем id хоста в переменную, чтобы не было проблем с правами доступа
      echo -e "\nDOCKER_USER=$(id -u):$(id -g)" # и выводим в консоль, чтобы можно было сразу увидеть этот параметр
    fi

    if ! docker network ls --format '{{.Name}}' | grep -q "^soundhead$"; then # если сеть soundhead ещё не существует
      docker network create soundhead # создаём сеть
    fi

    docker compose build # собираем образы
    docker compose run --rm composer install # Устанавливаем зависимости

    if [ "$isEnvCreatedFirst" -eq 1 ]; then # если файл .env создан сейчас впервые
      docker compose run --rm artisan key:generate # Генерируем ключ приложения
      docker compose run --rm artisan jwt:conf # Генерируем ключ и конфиг для jwt-авторизации
    fi

    docker compose up back -d
    docker compose run --rm artisan migrate --seed # Накатываем миграции и наполняем контентом
    
    echo "Initialization finished"
    echo -e "\e[32m\nProject is started in backend-only mode. Available at:\e[0m"
    echo -e "\e[1;33mhttp://localhost\e[0m"
    echo -e "\e[32mDocumentation:\e[0m"
    echo -e "\e[1;33mhttp://localhost/api/docs\e[0m"
    ;;

  remigrate)
    echo "Starting remigrate..."
    docker compose run --rm artisan migrate:fresh --seed # Очищаем БД, перезапускаем миграции и сидер
    echo "Finished"
    ;;

  up)
    echo "Up docker full-stack mode..."
    docker compose up nginx -d ${@:2}
    echo -e "\e[32m\nProject is started! Available at:\e[0m"
    echo -e "\e[1;33mhttp://localhost\e[0m"
    echo -e "\e[32mDocumentation:\e[0m"
    echo -e "\e[1;33mhttp://localhost/api/docs\e[0m"
    ;;

  back)
    echo "Up docker backend-only mode..."
    docker compose up back -d ${@:2}
    echo -e "\e[32m\nProject is started! Available at:\e[0m"
    echo -e "\e[1;33mhttp://localhost\e[0m"
    ;;

  down)
    echo "Down docker..."
    docker compose down --remove-orphans ${@:2}
    ;;

  art)
    echo "Starting artisan..."
    docker compose run --rm artisan ${@:2}
    echo "Finished"
    ;;

  comp)
    echo "Starting composer..."
    docker compose run --rm composer ${@:2}
    echo "Finished"
    ;;

  test)
    echo "Runing tests..."
    docker compose run --rm artisan test ${@:2}
    echo "Finished"
    ;;

  *)
    echo ""
    echo "  Usage: $0 {command} {params}"
    echo ""
    echo "    List of commands:"
    echo ""
    echo "      init — initialize project"
    echo "      up — up docker containers in full-stack mode"
    echo "      back — up docker containers in backend-only mode"
    echo "      down — down docker containers"
    echo "      art — run php artisan"
    echo "      comp — run composer"
    echo "      test — run tests"
    echo "      remigrate — migrate:fresh and seed"
    echo ""
    exit 1
    ;;

esac
