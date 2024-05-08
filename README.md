<p align="center"><img src="https://raw.githubusercontent.com/Rib0v/soundhead_back/7dfe94fc7ff10fbd7d23e79adc7a3e894df1dbf6/storage/app/laravel-logo.svg" width="400" alt="Laravel Logo"></p>

# О проекте

Backend-часть интернет-магазина на `Laravel`. Связь с фронтендом осуществляется по `REST API`. Аутентификация реализована через самописный `JWT` сервис с раздельными `access` и `refresh` токенами. Для кеширования используется `Redis`.

**Swagger-спецификация** доступна по маршруту:

```
/api/docs
```

[[Frontend здесь](https://github.com/Rib0v/soundhead_front)]

# Как развернуть локально

*Примечание: для запуска приложения требуется* `redis` *и расширение* `phpredis`.

Устанавливаем зависимости:

```bash
composer install
```

Копируем файл конфига:

```bash
cp .env.example .env
```

Генерируем ключ приложения:

```bash
php artisan key:generate
```

Генерируем ключ и конфиг для jwt-авторизации:

```bash
php artisan jwt:conf
```

Создаём в `/public` симлинк на `storage/app/public/` 

```bash
php artisan storage:link
```

Накатываем миграции и наполняем контентом:

```bash
php artisan migrate --seed
```

Запускаем сервер:

```bash
php artisan serve
```

# Как развернуть в Docker

### Различия в ОС

Все команды написаны для `Linux` системы. В `Windows` и `Mac OS` необходимо заменить `docker compose` на `docker-compose`, а `sudo` писать не обязательно. В `Windows` все команды выполнять в среде `WSL`.

### Установка

Копируем файл конфига:

```bash
cp .env.doc.example .env
```

Записываем id хоста в переменную, чтобы не было проблем с правами доступа к файлам:

```bash
echo -e "\nDOCKER_USER=$(id -u):$(id -g)" >> .env
```

Устанавливаем зависимости:

```bash
sudo docker compose run --rm composer install
```

Генерируем ключ приложения:

```bash
sudo docker compose run --rm artisan key:generate
```

Генерируем ключ и конфиг для jwt-авторизации:

```bash
sudo docker compose run --rm artisan jwt:conf
```

Накатываем миграции и наполняем контентом:

```bash
sudo docker compose run --rm artisan migrate --seed
```

### Запуск бэка отдельно

По умолчанию ресурс будет доступен на 80 порту `http://localhost/` 

```bash
sudo docker compose up lara
```

### Запуск совместно с фронтом

Сначала нужно запустить [frontend-часть](https://github.com/Rib0v/soundhead_front). После этого запускаем `nginx`. Сервер будет доступен на 80 порту `http://localhost/` 

```bash
sudo docker compose up nginx
```
