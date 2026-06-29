# Установка

## Быстрая установка

```bash
composer create-project pet/framework my-project
cd my-project
```

## Шаблон проекта (рекомендуется)

Для быстрого старта используйте готовый шаблон проекта:

```bash
git clone https://github.com/AlexNextProgramm/pet-sample-1.git my-project
cd my-project
composer install
```

Шаблон [`pet-sample-1`](https://github.com/AlexNextProgramm/pet-sample-1.git) включает:

- **Готовую структуру** приложения (`app/`, `view/`, `migrate/`, `socket/`, `routes.php`)
- **Настроенный `.env`** — отредактируйте под своё окружение
- **Базовые модели и контроллеры** — можно сразу добавлять свою логику
- **Примеры маршрутов** — GET, POST, группы, middleware
- **WebSocket-сервер** — готовый класс для чата/уведомлений
- **Миграции** — SQL-файлы для создания таблиц
- **Консольные команды** — `php pet serve` для запуска dev-сервера

После клонирования:

1. Отредактируйте `.env` (БД, URL, пути)
2. Запустите миграции: `php pet migrate`
3. Запустите dev-сервер: `php pet serve`
4. Откройте `http://localhost:8000`

Создайте файл `.env` в корне проекта (если используете `composer create-project`):

```env
DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=database
DB_USER=root
DB_PASSWORD=
URLDEV=http://localhost:8000
PUBLIC_DIR=dist
APP=app
VIEW_DIR=view
MIGRATE_DIR=migrate
SOCKET_DIR=socket
ENV=.env
LOG=log.txt
SVG=svg
IMG_RELAT=img
UPLOADS=uploads
```

## Требования

- PHP 8.1 или выше
- PDO-драйвер для MySQL/MariaDB
- Composer
- Node.js и npm (для сборки фронтенда)