# PET Framework (для проектов)

**PET** — легковесный PHP-фреймворк для веб-приложений с поддержкой маршрутизации, ORM, WebSocket, миграций, консольных команд, Blade-шаблонизатора и гибкой системы шаблонов.

## Возможности

- **Маршрутизация** с поддержкой GET, POST, PUT, DELETE, OPTIONS
- **Гибкие URL** (`{param}`) и wildcard (`/*`)
- **ORM** с построителем запросов (Active Record)
- **WebSocket** сервер на нативных PHP-сокетах
- **Миграции** базы данных
- **Middleware** для обработки запросов
- **Blade-шаблонизатор** — Laravel-подобный шаблонизатор с секциями, стеками, компонентами и макетами
- **Шаблонизатор** с экранированием XSS
- **Консольные команды** (CLI) с цветным выводом, таблицами, прогресс-барами и гиперссылками
- **Генерация моделей** через Blade-шаблоны (`php pet make:model`)
- **FTP-деплой**
- **Git-мониторинг** с автосборкой
- **Поддержка JSON API**

## Документация

| Раздел | Описание |
|--------|----------|
| [Установка и шаблон проекта](docs/installation.md) | Установка через Composer, клонирование шаблона `pet-sample-1`, требования |
| [Конфигурация](docs/configuration.md) | Параметры `.env`, структура проекта |
| [Маршрутизация](docs/routing.md) | GET/POST/PUT/DELETE/OPTIONS, параметры в URL, wildcard, middleware |
| [Контроллеры](docs/controllers.md) | Базовый контроллер, примеры |
| [Модели и ORM](docs/models.md) | Определение модели, Fluent API, CRUD, транзакции, Join |
| [Шаблоны (View)](docs/views.md) | Отображение шаблонов, Blade-шаблонизатор, секции, стеки, компоненты |
| [Middleware](docs/middleware.md) | Создание middleware, цепочки обработки |
| [WebSocket](docs/websocket.md) | Нативный WebSocket-сервер на PHP-сокетах |
| [Миграции](docs/migrations.md) | SQL-миграции с отслеживанием по хешу |
| [Консольные команды](docs/console.md) | Стартовый файл `pet`, список команд, Console API |
| [Работа с запросами](docs/requests.md) | HTTP-запросы, input, files, headers |
| [Cookie и Сессии](docs/cookies-sessions.md) | Работа с куки и сессиями |
| [Обработка ошибок](docs/errors.md) | Логирование, исключения, HTTP-ошибки, ответы |
| [Вспомогательные функции](docs/helpers.md) | Глобальные функции, Tools |
| [Файловая библиотека](docs/file-library.md) | File, FileCollection, FileManager, Storage, Image, MimeTypeDetector |
| [Модули](docs/modules.md) | PlusOfon (SMS), Imap (почта) |

## Быстрый старт

```bash
# Через Composer
composer create-project pet/framework my-project

# Или через шаблон (рекомендуется)
git clone https://github.com/AlexNextProgramm/pet-sample-1.git my-project
cd my-project
composer install
```

Подробнее — в разделе [Установка и шаблон проекта](docs/installation.md).

## Лицензия

MIT
