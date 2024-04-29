<p align="center"><img src="https://raw.githubusercontent.com/Rib0v/soundhead_back/7dfe94fc7ff10fbd7d23e79adc7a3e894df1dbf6/storage/app/laravel-logo.svg" width="400" alt="Laravel Logo"></p>

# О проекте

Backend-часть интернет-магазина на `Laravel`. Связь с фронтендом осуществляется по `REST API`. Аутентификация реализована через самописный `JWT` сервис с раздельными `access` и `refresh` токенами.

**Swagger-спецификация** доступна по маршруту:

```
/api/docs
```

[[Frontend здесь](https://github.com/Rib0v/soundhead_front)]

# Как развернуть локально

```bash
# Устанавливаем зависимости
composer install

# Копируем файл конфига
cp .env.example .env

# в .env указываем порт, на котором будет
# запущено приложение. Это нужно только для
# правильной генерации путей к фотографиям.
APP_PORT=8000

# Генерируем ключ приложения
php artisan key:generate

# Генерируем ключ и конфиг для jwt-авторизации
php artisan jwt:conf

# Создаём в /public симлинк на storage/app/public/
php artisan storage:link

# Накатываем миграции + наполняем контентом
php artisan migrate --seed

# Запускаем сервер
php artisan serve
```

# Как развернуть в Docker

### Различия в ОС

Все команды написаны для `Linux` системы. В `Windows` и `Mac OS` необходимо заменить `docker compose` на `docker-compose`, а `sudo` писать не обязательно. В `Windows` все команды выполнять в среде `WSL`.

### Установка

```bash
# Копируем файл конфига
cp .env.example .env

# Записываем id хоста в переменную, чтобы
# не было проблем с правами доступа к файлам
echo -e "\nDOCKER_USER=$(id -u):$(id -g)" >> .env

# Устанавливаем зависимости
sudo docker compose run --rm composer install

# Генерируем ключ приложения
sudo docker compose run --rm artisan key:generate

# Генерируем ключ и конфиг для jwt-авторизации
sudo docker compose run --rm artisan jwt:conf

# Накатываем миграции + наполняем контентом
sudo docker compose run --rm artisan migrate --seed
```

### Запуск бэка отдельно

По умолчанию ресурс будет доступен на 80 порту `http://localhost/` 

```bash
# Запускаем сервер - только бэк 
sudo docker compose up lara
```

### Запуск совместно с фронтом

Сначала нужно запустить [frontend-часть](https://github.com/Rib0v/soundhead_front). После этого запускаем nginx. Сервер будет доступен на 80 порту `http://localhost/`

```bash
# Стартуем сервер, когда фронтенд уже запущен
sudo docker compose up nginx
```
