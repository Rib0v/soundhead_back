<p align="center"><img src="https://raw.githubusercontent.com/Rib0v/soundhead_back/7dfe94fc7ff10fbd7d23e79adc7a3e894df1dbf6/storage/app/laravel-logo.svg" width="400" alt="Laravel Logo"></p>

# О проекте

Backend-часть интернет-магазина на `Laravel`. Связь с фронтендом осуществляется по `REST API`. Аутентификация реализована через самописный `JWT` сервис с раздельными `access` и `refresh` токенами. Данные кешируются с применением `Redis`.

**Swagger-спецификация** доступна по маршруту:

```
/api/docs
```

[[Frontend здесь](https://github.com/Rib0v/soundhead_front)]

# Как развернуть в Docker

### Инициализация

При первом запуске выполнить в корневой папке проекта:

```bash
./d init
```

Эта команда установит зависимости, накатит миграции и запустит приложение в режиме `только-backend` на 80 порту [http://localhost/](http://localhost/)

### Запуск бэка отдельно

```bash
./d back
```

### Запуск совместно с фронтом

Сначала нужно запустить [frontend-часть](https://github.com/Rib0v/soundhead_front). После этого выполнить:

```bash
./d up
```

Приложение будет доступно на 80 порту [http://localhost/](http://localhost/)

### Завершение

```bash
./d down
```

### Список команд

Показать полный список доступных команд:

```bash
./d
```
