# PET Framework (для проектов)

**PET** — легковесный PHP-фреймворк для веб-приложений с поддержкой маршрутизации, ORM, WebSocket, миграций, консольных команд и гибкой системы шаблонов.

---

## Содержание

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Шаблон проекта](#шаблон-проекта-рекомендуется)
- [Конфигурация](#конфигурация)
- [Структура проекта](#структура-проекта)
- [Маршрутизация](#маршрутизация)
- [Контроллеры](#контроллеры)
- [Модели и ORM](#модели-и-orm)
  - [Определение модели](#определение-модели)
  - [Конструктор](#конструктор)
  - [Построитель запросов](#построитель-запросов-fluent-api)
  - [Выборка](#выборка-select)
  - [Условия](#условия-where)
  - [Сортировка и группировка](#сортировка-и-группировка)
  - [Пагинация](#лимит-и-пагинация)
  - [Join](#join)
  - [Вставка](#вставка-insert)
  - [Обновление](#обновление-update)
  - [Удаление](#удаление-delete)
  - [Работа с моделью](#работа-с-загруженной-моделью)
  - [Транзакции](#транзакции)
  - [Прямые SQL](#прямые-sql-запросы)
- [Шаблоны (View)](#шаблоны-view)
- [Middleware](#middleware)
- [WebSocket](#websocket)
- [Миграции](#миграции)
- [Консольные команды](#консольные-команды)
  - [Стартовый файл](#стартовый-файл)
  - [Запуск команд](#запуск-команд)
- [Работа с запросами](#работа-с-запросами)
- [Cookie и Сессии](#cookie-и-сессии)
- [Обработка ошибок](#обработка-ошибок)
- [Вспомогательные функции](#вспомогательные-функции)
- [Файловая библиотека (File)](#файловая-библиотека-file)
  - [File](#класс-file)
  - [FileCollection](#filecollection)
  - [FileManager](#filemanager)
  - [Storage](#storage)
  - [Image](#image)
  - [MimeTypeDetector](#mimetypedetector)
  - [FileException](#fileexception)
- [Модули (Module)](#модули-module)
  - [PlusOfon](#plusofon--отправка-sms)
  - [Imap](#imap--работа-с-почтой)
- [Лицензия](#лицензия)

---

## Возможности

- **Маршрутизация** с поддержкой GET, POST, PUT, DELETE, OPTIONS
- **Гибкие URL** (`{param}`) и wildcard (`/*`)
- **ORM** с построителем запросов (Active Record)
- **WebSocket** сервер на нативных PHP-сокетах
- **Миграции** базы данных
- **Middleware** для обработки запросов
- **Шаблонизатор** с экранированием XSS
- **Консольные команды** (CLI)
- **FTP-деплой**
- **Git-мониторинг** с автосборкой
- **Поддержка JSON API**

---

## Требования

- PHP 8.1 или выше
- PDO-драйвер для MySQL/MariaDB
- Composer
- Node.js и npm (для сборки фронтенда)

---

## Установка

```bash
composer create-project pet/framework my-project
cd my-project
```

### Шаблон проекта (рекомендуется)

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

---

## Конфигурация

Фреймворк использует файл `.env` для конфигурации. Все константы автоматически определяются через функцию [`setConstantEnv()`](function.php:43).

### Основные параметры

| Параметр | Описание                          | По умолчанию   |
| ---------------- | ----------------------------------------- | ------------------------- |
| `DB_TYPE`      | Тип БД (mysql)                       | `mysql`                 |
| `DB_HOST`      | Хост БД                             | `127.0.0.1`             |
| `DB_PORT`      | Порт БД                             | `3306`                  |
| `DB_NAME`      | Имя БД                               | —                        |
| `DB_USER`      | Пользователь БД             | —                        |
| `DB_PASSWORD`  | Пароль БД                         | —                        |
| `URLDEV`       | URL разработки                  | `http://localhost:8000` |
| `PUBLIC_DIR`   | Публичная директория   | `dist`                  |
| `APP`          | Директория приложения | `app`                   |
| `VIEW_DIR`     | Директория шаблонов     | `view`                  |
| `MIGRATE_DIR`  | Директория миграций     | `migrate`               |
| `SOCKET_DIR`   | Директория сокетов       | `socket`                |
| `ENV`          | Имя файла .env                    | `.env`                  |
| `LOG`          | Файл лога ошибок            | `log.txt`               |

Поддерживается **сериализация констант** — в значениях `.env` можно ссылаться на другие константы через `[ИМЯ_КОНСТАНТЫ]`.

---

## Структура проекта

```
├── Command/          # Консольные команды
│   ├── Console/      # Вывод в консоль
│   ├── FTP/          # FTP-деплой
│   └── Command.php   # Главный обработчик команд
├── Cookie/           # Работа с куки
├── DataBase/         # Слой работы с БД (PDO)
│   ├── DB.php        # Базовый класс подключения
│   ├── Delete.php    # Trait удаления
│   ├── Insert.php    # Trait вставки
│   ├── Select.php    # Trait выборки
│   └── Update.php    # Trait обновления
├── Errors/           # Обработка ошибок
├── File/             # Файловая библиотека
│   ├── File.php              # Объектное представление файла
│   ├── FileCollection.php    # Коллекция файлов
│   ├── FileManager.php       # Менеджер дисков и утилиты
│   ├── Storage.php           # Файловое хранилище
│   ├── Image.php             # Работа с изображениями (GD)
│   ├── MimeTypeDetector.php  # Определение MIME-типов
│   └── Exception/            # Исключения файловых операций
├── Frontend/         # Фронтенд-ресурсы
├── Git/              # Git-мониторинг
├── Migration/        # Миграции БД
├── Model/            # Базовый класс модели
├── Module/           # Готовые интеграции с внешними сервисами
│   ├── PlusOfon.php  # Отправка SMS через PlusOfon
│   └── Imap.php      # Работа с IMAP-почтой
├── Request/          # Работа с HTTP-запросами
├── Router/           # Маршрутизация
├── Session/          # Работа с сессиями
├── Socket/           # WebSocket сервер
├── Tools/            # Утилиты
├── View/             # Шаблонизатор
├── chain/            # Вспомогательные функции
├── Controller.php    # Базовый контроллер
├── Territory.php     # Отладка/трассировка
└── function.php      # Глобальные функции
```

---

## Маршрутизация

Маршруты определяются в файле `routes.php` вашего приложения.

### Простые маршруты

```php
use Pet\Router\Router;

Router::get('/', function() {
    return 'Hello, World!';
});

Router::post('/user', [UserController::class, 'store']);
```

### Поддерживаемые HTTP-методы

- [`Router::get()`](Router/Router.php:22) — GET-запросы
- [`Router::post()`](Router/Router.php:28) — POST-запросы
- [`Router::put()`](Router/Router.php:40) — PUT-запросы
- [`Router::delete()`](Router/Router.php:34) — DELETE-запросы
- [`Router::options()`](Router/Router.php:46) — OPTIONS-запросы

### Гибкие URL (параметры в пути)

```php
Router::get('/user/{id}', [UserController::class, 'show']);
// /user/42 → $id = 42
```

Параметры извлекаются через функцию [`supple()`](chain/chain.php:64):

```php
function show() {
    $id = supple('id'); // 42
}
```

### Wildcard маршруты

```php
Router::get('/admin/*', [AdminController::class, 'handle']);
```

### Именованные маршруты и группы

```php
Router::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->group('admin');
```

### Middleware

```php
use Pet\Router\Middleware;

Middleware::middleware(AuthMiddleware::class)
    ->get('/profile', [ProfileController::class, 'index'])
    ->post('/profile', [ProfileController::class, 'update']);
```

---

## Контроллеры

Контроллеры наследуются от [`Pet\Controller`](Controller.php).

```php
namespace App\Controller;

use Pet\Controller;

class UserController extends Controller
{
    public function index() {
        return ['users' => User::all()];
    }

    public function store() {
        $data = attrs(); // все входные данные
        // ...
    }
}
```

Базовый контроллер предоставляет метод [`saveFile()`](Controller.php:15) для загрузки файлов.

---

## Модели и ORM

Модели наследуются от [`Pet\Model\Model`](Model/Model.php) и используют traits [`Select`](DataBase/Select.php), [`Insert`](DataBase/Insert.php), [`Update`](DataBase/Update.php), [`Delete`](DataBase/Delete.php) для операций CRUD. Базовый класс [`DB`](DataBase/DB.php) предоставляет подключение к БД через PDO.

### Определение модели

```php
use Pet\Model\Model;

class User extends Model
{
    protected string $table = 'users';
    public array $hidden = ['password']; // скрытые поля при data()
    protected bool $allowMultiple = false; // true — исключение при >1 строки
    protected static ?string $connection = null; // имя подключения для модели
}
```

### Конструктор

```php
// По ID — загружает запись
$user = new User(1);

// По массиву условий
$user = new User(['email' => 'john@example.com']);

// Создание, если не существует
$user = new User(['name' => 'John'], true); // $isNotExistCreate = true

// С указанием подключения к БД
$user = new User(1, false, 'analytics');
```

### Основные операции

```php
// Поиск
$users = (new User())->find(['role' => 'admin']);
$user  = (new User(1)); // по ID

// Создание
$user = new User(['name' => 'John', 'email' => 'john@example.com'], true);
// или
$id = $user->create(['name' => 'John', 'email' => 'john@example.com']);

// Обновление
$user->set('name', 'Jane');
// или
$user->set(['name' => 'Jane', 'email' => 'jane@example.com']);

// Удаление
$user->delete();

// Массовое удаление
$user->findDelete(['role' => 'guest']);
```

### Построитель запросов (Fluent API)

Все методы построителя возвращают `$this`, что позволяет выстраивать цепочки вызовов.

```php
$users = (new User())
    ->select('id', 'name')
    ->where('role = ?', ['admin'])
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->offset(0)
    ->fetch();
```

### Полный список методов

#### Выборка (Select)

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`select()`](DataBase/Select.php:23) | Указать поля для выборки (`select('id', 'name')`) |
| [`selectDistinct()`](DataBase/Select.php:48) | Выборка с DISTINCT |
| [`find()`](Model/Model.php:106) | Поиск по массиву полей, возвращает массив результатов |
| [`findM()`](Model/Model.php:128) | Поиск, возвращает массив моделей |
| [`findAll()`](Model/Model.php:140) | Все записи из таблицы |
| [`findBy()`](Model/Model.php:153) | Поиск по полю и значению (`findBy('email', 'a@b.com')`) |
| [`findByM()`](Model/Model.php:166) | Поиск по полю, возвращает массив моделей |
| [`findById()`](DataBase/Select.php:453) | Поиск по ID, возвращает одну запись |
| [`first()`](DataBase/Select.php:441) | Первая запись |
| [`pluck()`](Model/Model.php:179) | Массив значений одного поля (`pluck('email')`) |
| [`count()`](DataBase/Select.php:74) | Количество записей (`count('*', 'total')`) |
| [`sum()`](DataBase/Select.php:87) | Сумма значений поля |
| [`avg()`](DataBase/Select.php:100) | Среднее значение поля |
| [`min()`](DataBase/Select.php:113) | Минимальное значение поля |
| [`max()`](DataBase/Select.php:126) | Максимальное значение поля |
| [`fetch()`](DataBase/DB.php:138) | Выполнить запрос и вернуть результат |
| [`chunk()`](Model/Model.php:192) | Обработка записей частями по N штук |

#### Условия (WHERE)

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`where()`](DataBase/Select.php:197) | Условие (`where('age', 18, '>')` или `where('role = ?', ['admin'])`) |
| [`whereAdd()`](DataBase/Select.php:217) | Добавить условие с произвольным разделителем |
| [`whereRaw()`](DataBase/Select.php:232) | Сырой SQL в WHERE |
| [`whereId()`](DataBase/Select.php:387) | По ID (`whereId(42)`) |
| [`whereNull()`](DataBase/Select.php:243) | `WHERE column IS NULL` |
| [`whereNotNull()`](DataBase/Select.php:254) | `WHERE column IS NOT NULL` |
| [`whereBetween()`](DataBase/Select.php:267) | `WHERE column BETWEEN x AND y` |
| [`whereIn()`](DataBase/Select.php:279) | `WHERE column IN (a, b, c)` |
| [`and()`](DataBase/Select.php:292) | Дополнительное условие с AND |
| [`or()`](DataBase/Select.php:304) | Дополнительное условие с OR |

#### Сортировка и группировка

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`orderBy()`](DataBase/Select.php:317) | Сортировка (`orderBy('created_at', 'DESC')`) |
| [`orderByDesc()`](DataBase/Select.php:340) | Сортировка по убыванию |
| [`groupBy()`](DataBase/Select.php:351) | Группировка |
| [`having()`](DataBase/Select.php:374) | Фильтрация после GROUP BY |

#### Лимит и пагинация

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`limit()`](DataBase/Select.php:401) | Ограничение количества записей |
| [`offset()`](DataBase/Select.php:414) | Смещение |
| [`page()`](DataBase/Select.php:428) | Пагинация (`page(2, 20)` — страница 2, по 20 записей) |

#### Join

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`join()`](DataBase/Select.php:152) | Присоединение таблицы (`join('posts', 'INNER')`) |
| [`on()`](DataBase/Select.php:169) | Условие JOIN |

```php
$posts = (new Post())
    ->select('posts.*', 'users.name as author')
    ->join('users', 'LEFT')
    ->on(['posts.user_id', 'users.id', '='])
    ->fetch();
```

Поддерживаются множественные JOIN:

```php
$data = (new Order())
    ->select('orders.*', 'users.name', 'products.title')
    ->join('users')
    ->on(['orders.user_id', 'users.id', '='])
    ->join('products', 'LEFT')
    ->on(['orders.product_id', 'products.id', '='])
    ->where('orders.status', 'paid')
    ->fetch();
```

#### Вставка (Insert)

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`insert()`](DataBase/Insert.php:16) | Вставка записи, возвращает bool |
| [`create()`](DataBase/Insert.php:35) | Вставка с возвратом ID и установкой info |
| [`insertBatch()`](DataBase/Insert.php:51) | Массовая вставка нескольких записей |
| [`insertOnDuplicate()`](DataBase/Insert.php:79) | INSERT с ON DUPLICATE KEY UPDATE |
| [`replace()`](DataBase/Insert.php:112) | REPLACE INTO (MySQL) |

```php
// Простая вставка
$id = $user->create(['name' => 'John', 'email' => 'john@example.com']);

// Массовая вставка
$user->insertBatch([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);

// Вставка или обновление при дубликате
$user->insertOnDuplicate(
    ['email' => 'john@example.com', 'name' => 'John', 'visits' => 1],
    ['name', 'visits'] // поля для обновления
);

// REPLACE
$user->replace(['id' => 1, 'name' => 'John', 'email' => 'john@example.com']);
```

#### Обновление (Update)

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`update()`](DataBase/Update.php:17) | Построитель UPDATE, возвращает `$this` для цепочки |
| [`updateBatch()`](DataBase/Update.php:34) | Массовое обновление через CASE |
| [`increment()`](DataBase/Update.php:73) | Увеличение поля на значение |
| [`decrement()`](DataBase/Update.php:88) | Уменьшение поля на значение |
| [`set()`](Model/Model.php:233) | Установка полей текущей модели (требует info и id) |

```php
// Обновление с условием
$user->update(['name' => 'Jane'])->whereId(1)->execute();

// Инкремент/декремент
$post->increment('views', 1)->whereId(42)->execute();
$product->decrement('stock', 1)->whereId(10)->execute();

// Массовое обновление
$user->updateBatch([
    ['id' => 1, 'name' => 'John', 'role' => 'admin'],
    ['id' => 2, 'name' => 'Jane', 'role' => 'user'],
]);

// Установка полей загруженной модели
$user = new User(1);
$user->set('name', 'Jane');
$user->set(['name' => 'Jane', 'email' => 'jane@example.com']);
```

#### Удаление (Delete)

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`delete()`](DataBase/Delete.php:16) | Удаление текущей записи или построитель DELETE |
| [`deleteById()`](DataBase/Delete.php:33) | Удаление по ID |
| [`deleteWhere()`](DataBase/Delete.php:48) | Удаление с условием |
| [`truncate()`](DataBase/Delete.php:60) | Очистка таблицы (TRUNCATE) |
| [`softDelete()`](DataBase/Delete.php:73) | Мягкое удаление (устанавливает `deleted_at = NOW()`) |
| [`restore()`](DataBase/Delete.php:90) | Восстановление soft-deleted записи |
| [`findDelete()`](Model/Model.php:347) | Найти и удалить |

```php
// Удаление текущей записи
$user = new User(1);
$user->delete();

// Удаление по ID
$user->deleteById(1);

// Удаление с условием
$user->deleteWhere('role', 'guest');

// Мягкое удаление
$user->softDelete()->whereId(1)->execute();

// Восстановление
$user->restore()->whereId(1)->execute();

// Очистка таблицы
$user->truncate();

// Найти и удалить
$user->findDelete(['role' => 'guest']);
```

#### Работа с загруженной моделью

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`data()`](Model/Model.php:328) | Данные модели с учётом `$hidden` |
| [`get()`](DataBase/DB.php:276) | Значение поля (`$user->get('name')`) |
| [`__get()`](Model/Model.php:82) | Магический доступ (`$user->name`) |
| [`__set()`](Model/Model.php:94) | Магическая установка (`$user->name = 'John'`) |
| [`exist()`](Model/Model.php:263) | Проверка наличия данных в модели |
| [`isInfo()`](DataBase/DB.php:265) | Проверка, загружены ли данные |
| [`fresh()`](Model/Model.php:365) | Обновить данные из БД |
| [`reboot()`](Model/Model.php:249) | Перезагрузить модель по ID |
| [`toArray()`](Model/Model.php:378) | Преобразовать в массив (алиас `data()`) |
| [`getInfo()`](DataBase/DB.php:297) | Получить весь массив info |

```php
$user = new User(1);

// Доступ к полям
echo $user->get('name');    // "John"
echo $user->name;           // "John" (магический __get)
$user->name = 'Jane';       // магический __set (вызов set())

// Данные с исключением скрытых полей
$data = $user->data();      // ['id' => 1, 'name' => 'John'] — без 'password'
$array = $user->toArray();  // то же самое

// Проверки
if ($user->exist()) { /* запись существует */ }
if ($user->isInfo()) { /* данные загружены */ }

// Обновить из БД
$user->fresh();

// Перезагрузить
$user->reboot();
```

#### Условные операции

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`ifExistSetOrCreate()`](Model/Model.php:280) | Обновить если существует, иначе создать |
| [`ifExistDelete()`](Model/Model.php:307) | Удалить если существует |

```php
// Обновить или создать
$user->ifExistSetOrCreate(
    ['name' => 'John', 'email' => 'john@example.com'],
    ['email' => 'john@example.com'] // условие поиска
);

// Удалить если существует
$user->ifExistDelete(['role' => 'guest']);
```

#### Статические методы

| Метод | Описание |
|---------------------|-------------------------------------------------------|
| [`createNew()`](Model/Model.php:389) | Создать запись и вернуть модель |

```php
$user = User::createNew(['name' => 'John', 'email' => 'john@example.com']);
// Вернёт null при неудаче
```

#### Настройка таблицы и алиаса

```php
$user = (new User())
    ->setTable('users')           // установить таблицу
    ->setTableAlias('u');         // установить псевдоним
```

#### Множественные подключения к БД

Модель поддерживает работу с разными базами данных:

```php
// Через статическое свойство
class Analytics extends Model
{
    protected static ?string $connection = 'analytics';
}

// Через конструктор
$user = new User(1, false, 'analytics');

// Через метод
$user->setConnection('analytics');
```

#### Транзакции

Доступны через базовый класс [`DB`](DataBase/DB.php):

```php
$user = new User();

try {
    $user->beginTransaction();

    $user->create(['name' => 'John', 'balance' => 100]);
    $order->create(['user_id' => 1, 'amount' => 50]);

    $user->commit();
} catch (\Throwable $e) {
    $user->rollback();
}

// Проверка активности транзакции
$user->inTransaction();
```

#### Прямые SQL-запросы

```php
$result = $user->q("SELECT * FROM users WHERE role = 'admin'")->fetchAll();

// Построение запроса вручную
$user->strQuery = "SELECT * FROM users WHERE id = 1";
$data = $user->fetch(false);
```

#### Получение мета-информации

```php
$user->getTableName();      // имя таблицы
$user->getDbName();         // имя базы данных
$user->lastInsertId();      // последний вставленный ID
$user->endError();          // последняя ошибка
$user->isTable();           // проверка существования таблицы migrate
$user->getConnectionName(); // имя текущего подключения
```

---

## Шаблоны (View)

Шаблонизатор находится в [`Pet\View\View`](View/View.php).

### Отображение шаблона

```php
use Pet\View\View;

View::open('user.profile', ['name' => 'John', 'age' => 30]);
// загрузит view/user/profile.php
```

### Передача данных

```php
View::append(['title' => 'Главная']);
View::appendHtmlspecialchars(['user_input' => $unsafeData]); // XSS-защита
```

### Получение HTML без вывода

```php
$html = View::getTemplate('email.welcome', ['name' => 'John']);
```

### Вспомогательная функция

```php
view('user.profile', ['name' => 'John']);
```

---

## Middleware

Middleware создаётся наследованием от [`Pet\Router\Middleware`](Router/Middleware.php).

```php
use Pet\Router\Middleware;
use Pet\Request\Request;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request) {
        if (!isAuth()) {
            return false; // блокирует выполнение маршрута
        }
    }
}
```

Middleware может вернуть `false` для прерывания цепочки или массив/строку для ответа.

---

## WebSocket

Фреймворк поддерживает нативные WebSocket-серверы.

### Создание сокет-сервера

```php
use Pet\Socket\Socket;
use Pet\Socket\ResorceSocket;

class ChatServer extends Socket
{
    public function __construct() {
        $this->port = 8080;
    }

    public function evConnect(ResorceSocket $resource): void {
        // Новое подключение
    }

    public function evDisconnect(ResorceSocket $resource): void {
        // Отключение
    }

    public function evData(ResorceSocket $resource): void {
        $message = $resource->getMessange();
        // Обработка данных
    }

    public function evError(string $resource): void {
        // Ошибка
    }
}
```

### Запуск

```bash
php pet socket chat
```

---

## Миграции

Миграции — это SQL-файлы в директории `migrate/`.

### Создание миграции

Создайте SQL-файл в `migrate/`, например `001_create_users.sql`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Запуск миграций

```bash
php pet migrate
```

Фреймворк отслеживает выполненные миграции по хешу содержимого файла. Повторно не выполняет.

---

## Консольные команды

### Стартовый файл

В корне проекта должен находиться файл `pet` — точка входа для консольных команд:

```php
<?php

include './vendor/autoload.php';
include './vendor/pet/framework/function.php';
include './config.constant.php';

use Pet\Command\Command;

if ($argc == 1) die("Not arguments console \n");

$option = $argv;
unset($option[0]);
Command::init($option);
```

### Запуск команд

```bash
php pet [команда] [аргументы]
```

### Доступные команды

| Команда                         | Описание                                                         |
| -------------------------------------- | ------------------------------------------------------------------------ |
| [`serve`](Command/Command.php:64)       | Запускает встроенный PHP-сервер                 |
| [`migrate`](Command/Command.php:44)     | Выполняет миграции БД                                 |
| [`socket`](Command/Command.php:35)      | Запускает WebSocket-сервер                                |
| [`make:model`](Command/Command.php:47)  | Создаёт новую модель                                   |
| [`git-monitor`](Command/Command.php:52) | Мониторинг изменений в Git с автосборкой |
| [`git-update`](Command/Command.php:55)  | Pull изменений и сборка                                  |
| [`load`](Command/Command.php:38)        | Загрузка на сервер по FTP                              |
| [`load-diff`](Command/Command.php:41)   | Выгрузка только изменённых файлов          |
| [`info`](Command/Command.php:49)        | Информация о командах                                 |

---

## Работа с запросами

Класс [`Pet\Request\Request`](Request/Request.php) предоставляет доступ к данным HTTP-запроса.

```php
$request = request();

// Метод запроса
$method = $request->getMethod(); // GET, POST, etc.

// Входные данные
$name   = $request->input('name');
$all    = $request->input(); // все данные

// Файлы
$file   = $request->file('avatar');

// IP-адрес
$ip     = $request->ip();

// Заголовки
$header = $request->header; // массив заголовков

// Путь
$path   = $request->path; // /user/42

// Сегменты пути
$levels = levels(); // ['user', '42']
```

### Вспомогательные функции

- [`attr()`](chain/chain.php:24) — получить значение input
- [`attrs()`](chain/chain.php:33) — получить все входные данные
- [`request()`](chain/chain.php:42) — получить объект Request
- [`files()`](chain/chain.php:53) — получить файл
- [`supple()`](chain/chain.php:64) — получить параметр из URL
- [`levels()`](chain/chain.php:78) — сегменты пути
- [`original()`](chain/chain.php:88) — доменное имя

---

## Cookie и Сессии

### Cookie

```php
use Pet\Cookie\Cookie;

// Установка
Cookie::set(['theme' => 'dark', 'lang' => 'ru']);

// Получение
$theme = Cookie::get('theme');

// HttpOnly
Cookie::httpOnly(['token' => $token]);

// Удаление
Cookie::delete('theme');
```

### Сессии

```php
use Pet\Session\Session;

// Установка
Session::set(['user_id' => 42]);

// Получение
$userId = Session::get('user_id');
$all    = Session::get(); // все данные

// Уничтожение
Session::kill();
```

---

## Обработка ошибок

### Класс Errors

[`Pet\Errors\Errors`](Errors/Errors.php) автоматически логирует фатальные ошибки в файл, указанный в константе `LOG`.

### Исключения

```php
use Pet\Errors\AppException;

throw new AppException('Сообщение об ошибке', E_ERROR);
```

### HTTP-ошибки

```php
use Pet\Router\Error;

// Установка кастомного обработчика
Error::$events[404] = [NotFoundController::class, 'handle'];

// Вызов
Error::setHttp(404, 'Страница не найдена');
```

---

## Ответы

Класс [`Pet\Router\Response`](Router/Response.php) для формирования HTTP-ответов.

```php
use Pet\Router\Response;

// Редирект
Response::redirect('/dashboard');

// JSON-ответ
Response::set(Response::TYPE_JSON);
Response::echo(['status' => 'ok']);

// Ответ с завершением
Response::die('Error message');

// Код ответа
Response::code(201);
```

---

## Вспомогательные функции

### Глобальные функции

- [`dd()`](function.php:85) — дамп переменных с завершением
- [`env()`](function.php:21) — получение значения из .env
- [`svg()`](function.php:95) — вставка SVG
- [`img()`](function.php:106) — путь к изображению
- [`uploads()`](function.php:113) — путь к загруженному файлу
- [`dirEach()`](function.php:134) — итерация по директории

### Tools

- [`Tools::jsonDe()`](Tools/Tools.php:17) — безопасный JSON decode
- [`Tools::array_implode()`](Tools/Tools.php:52) — имплод ассоциативного массива
- [`Tools::is_assos()`](Tools/Tools.php:67) — определение типа массива
- [`Tools::strRepalceFile()`](Tools/Tools.php:92) — замена в файле
- [`Tools::filter()`](Tools/Tools.php:101) — фильтрация массива с ключами
- [`Tools::scan()`](Tools/Tools.php:109) — сканирование директории

---

## Файловая библиотека (File)

Фреймворк предоставляет полноценную объектную файловую библиотеку в пространстве имён [`Pet\File`](File/). Она включает классы для работы с отдельными файлами, коллекциями, хранилищами, изображениями и MIME-типами.

### Класс File

[`Pet\File\File`](File/File.php) — объектное представление отдельного файла в файловой системе.

```php
use Pet\File\File;

$file = new File('/path/to/file.txt');

// Метаданные
$file->path();              // /path/to/file.txt
$file->name();              // file.txt
$file->filename();          // file
$file->extension();         // txt
$file->dirname();           // /path/to
$file->size();              // размер в байтах
$file->sizeFormatted();     // "1.23 MB"
$file->mimeType();          // "text/plain"
$file->hash();              // md5-хеш
$file->hash('sha256');      // sha256-хеш
$file->lastModified();      // Unix timestamp
$file->permissions();       // "0644"
$file->owner();             // UID владельца
$file->group();             // GID группы

// Проверки
$file->exists();
$file->isFile();
$file->isReadable();
$file->isWritable();
$file->isImage();
$file->isText();
$file->isArchive();
$file->isPdf();

// Чтение и запись
$content = $file->content();            // чтение всего файла
$file->put('new content');              // перезапись
$file->append('more content');          // добавление в конец
$file->prepend('prefix content');       // добавление в начало
$lines = $file->lines();                // чтение построчно

// Операции с файлами
$copied = $file->copy('/new/path.txt');     // копирование
$file->move('/new/path.txt');               // перемещение
$file->rename('newname.txt');               // переименование
$file->delete();                            // удаление

// Конвертация изображения в WebP
$webpFile = $file->convertImage(80);

// Из загруженного файла
$uploaded = File::fromUpload($_FILES['avatar']);

// Сериализация
$info = $file->toArray();
```

### FileCollection

[`Pet\File\FileCollection`](File/FileCollection.php) — коллекция файлов с поддержкой фильтрации, сортировки и массовых операций. Реализует `Countable` и `IteratorAggregate`.

```php
use Pet\File\FileCollection;

// Создание коллекции
$collection = new FileCollection([$file1, $file2]);

// Из glob-паттерна
$collection = FileCollection::fromGlob('/path/to/*.txt');

// Из директории
$collection = FileCollection::fromDirectory('/path/to/dir');
$collection = FileCollection::fromDirectory('/path/to/dir', '*.jpg');

// Из загруженных файлов (поддержка вложенных массивов)
$collection = FileCollection::fromUploadedFiles($_FILES['photos']);

// Фильтрация
$images    = $collection->images();
$texts     = $collection->texts();
$archives  = $collection->archives();
$byExt     = $collection->byExtension('jpg', 'png');
$byMime    = $collection->byMimeType('image/jpeg');
$large     = $collection->largerThan(1024 * 1024);  // больше 1MB
$small     = $collection->smallerThan(50000);        // меньше 50KB

// Сортировка
$sorted = $collection->sortByName();
$sorted = $collection->sortBySize(false);   // по убыванию
$sorted = $collection->sortByDate();
$sorted = $collection->sortByExtension();

// Массовые операции
$collection->each(fn(File $f) => /* ... */);
$collection->map(fn(File $f) => $f->name());
$collection->filter(fn(File $f) => $f->size() > 1000);
$collection->copyTo('/backup/');
$collection->moveTo('/archive/');
$collection->deleteAll();

// Агрегация
$collection->totalSize();           // суммарный размер
$collection->totalSizeFormatted();  // "15.42 MB"
$collection->names();               // массив имён
$collection->paths();               // массив путей
$collection->extensions();          // массив расширений
$collection->toArray();             // массив метаданных

// Доступ к элементам
$collection->first();
$collection->last();
$collection->get(3);
$collection->isEmpty();
count($collection);

// Итерация
foreach ($collection as $file) {
    echo $file->name();
}
```

### FileManager

[`Pet\File\FileManager`](File/FileManager.php) — синглтон-менеджер для управления дисками (Storage) и вспомогательных операций.

```php
use Pet\File\FileManager;

// Доступ к дискам
$local  = FileManager::disk('local');   // var/uploads/
$public = FileManager::disk('public');  // public_html/uploads/
$tmp    = FileManager::disk('tmp');     // sys_get_temp_dir()/pet-uploads/

// Регистрация кастомного диска
FileManager::registerDisk('s3', new Storage('/s3/path', '/s3-url'));

// Временные файлы
$tempFile = FileManager::temp('content', 'txt');

// Управление директориями
FileManager::ensureDirectory('/path/to/dir');
FileManager::cleanDirectory('/path/to/dir');    // рекурсивная очистка
FileManager::copyDirectory('/from', '/to');     // рекурсивное копирование

// Поиск файлов
$files = FileManager::glob('/path/to/*.php');
$files = FileManager::find('/path/to', '*.txt');  // рекурсивный поиск

// Утилиты
FileManager::humanSize(1048576);            // "1.0 MB"
FileManager::sanitizeFilename('bad/name');  // "bad_name"
FileManager::uniqueFilename('/dir', 'file.txt');
FileManager::extension('image/jpeg');       // "jpg"
```

### Storage

[`Pet\File\Storage`](File/Storage.php) — абстракция файлового хранилища с привязкой к корневой директории и URL-префиксу.

```php
use Pet\File\Storage;

$storage = new Storage('/var/www/uploads', '/uploads');

// Сохранение загруженных файлов
$path = $storage->save($_FILES['file'], 'avatars');
// Возвращает относительный путь: "avatars/ab12cd34.jpg"

$path = $storage->saveWithOriginalName($_FILES['file'], 'docs');
// Сохраняет с оригинальным именем (санитизированным)

$path = $storage->saveContent('content', 'data/file.txt');
$path = $storage->saveFile($file, 'backup');

// URL и пути
$url  = $storage->url('avatars/ab12cd34.jpg');   // /uploads/avatars/ab12cd34.jpg
$path = $storage->path('avatars/ab12cd34.jpg');   // /var/www/uploads/avatars/ab12cd34.jpg

// Проверки
$storage->exists('avatars/ab12cd34.jpg');
$storage->size('avatars/ab12cd34.jpg');
$storage->mimeType('avatars/ab12cd34.jpg');
$storage->lastModified('avatars/ab12cd34.jpg');

// Удаление
$storage->delete('avatars/ab12cd34.jpg');
$storage->deleteDirectory('old_folder');

// Список файлов
$storage->files();              // файлы в корне хранилища
$storage->files('avatars');     // файлы в поддиректории
$storage->allFiles();           // рекурсивно все файлы
$storage->directories();        // поддиректории

// Управление
$storage->makeDirectory('new_dir');
$storage->copy('from/file.txt', 'to/file.txt');
$storage->move('from/file.txt', 'to/file.txt');

// Отдача файла браузеру
$storage->serve('avatars/ab12cd34.jpg');     // вывод с Content-Type
$storage->download('avatars/ab12cd34.jpg');  // принудительное скачивание

// Получение объекта File
$file = $storage->file('avatars/ab12cd34.jpg');

// Storage как middleware для отдачи статики
// Router::get('/uploads/*', Storage::disk('local'));
```

### Image

[`Pet\File\Image`](File/Image.php) — класс для работы с изображениями через GD. Поддерживает изменение размера, обрезку, поворот, водяные знаки и конвертацию.

```php
use Pet\File\Image;

// Загрузка
$image = new Image('photo.jpg');
$image = Image::fromFile($file);
$image = Image::fromString($binaryData);
$image = Image::create(800, 600, '#ff0000');  // создание нового

// Метаданные
$image->width();
$image->height();
$image->mimeType();
$image->path();

// Изменение размера
$image->resize(400, 300);               // точный размер
$image->resize(400, null, true);        // по ширине с сохранением пропорций
$image->resizeToWidth(400);             // по ширине
$image->resizeToHeight(300);            // по высоте

// Обрезка
$image->crop(10, 10, 200, 150);         // произвольная область
$image->cropCenter(200, 200);           // центр
$image->cropThumbnail(150);             // квадратный превью

// Трансформации
$image->rotate(90, '#ffffff');          // поворот
$image->flipHorizontal();               // отражение по горизонтали
$image->flipVertical();                 // отражение по вертикали

// Водяной знак и текст
$image->watermark('logo.png', Image::BOTTOM_RIGHT, 10);
$image->text('(c) 2026', 5, '#000000', 10, 10);

// Сохранение
$image->save('output.jpg', 'jpeg', 90);
$image->saveAsJpeg('output.jpg', 90);
$image->saveAsPng('output.png');
$image->saveAsWebp('output.webp', 80);
$image->saveAsGif('output.gif');
$image->convertToWebp(80);  // конвертация рядом с оригиналом

// Base64
$dataUri = $image->toBase64();  // "data:image/png;base64,..."

// Доступ к GD-ресурсу
$gd = $image->getResource();
```

Константы позиций для водяного знака:

| Константа | Позиция |
|---------------------|---------------------|
| `Image::TOP_LEFT` | Верхний левый угол |
| `Image::TOP_CENTER` | Верхний центр |
| `Image::TOP_RIGHT` | Верхний правый угол |
| `Image::MIDDLE_LEFT` | Левый центр |
| `Image::MIDDLE_CENTER` | Центр |
| `Image::MIDDLE_RIGHT` | Правый центр |
| `Image::BOTTOM_LEFT` | Нижний левый угол |
| `Image::BOTTOM_CENTER` | Нижний центр |
| `Image::BOTTOM_RIGHT` | Нижний правый угол |

### MimeTypeDetector

[`Pet\File\MimeTypeDetector`](File/MimeTypeDetector.php) — определение MIME-типов по расширению, файлу, а также категоризация.

```php
use Pet\File\MimeTypeDetector;

// Определение
MimeTypeDetector::fromExtension('jpg');     // "image/jpeg"
MimeTypeDetector::fromFile('/path/to/file'); // по содержимому
MimeTypeDetector::extensionFor('image/jpeg'); // "jpg"

// Проверки
MimeTypeDetector::isImage('image/png');         // true
MimeTypeDetector::isVideo('video/mp4');         // true
MimeTypeDetector::isAudio('audio/mpeg');        // true
MimeTypeDetector::isText('text/plain');         // true
MimeTypeDetector::isArchive('application/zip'); // true
MimeTypeDetector::isDocument('application/pdf');// true
MimeTypeDetector::isExecutable('application/x-sh'); // true
MimeTypeDetector::isWebSafeImage('image/webp'); // true

// Валидация по белому списку (поддержка wildcard)
MimeTypeDetector::isAllowed(['image/*', 'application/pdf'], 'image/png'); // true

// Категории
MimeTypeDetector::category('image/jpeg');   // "image"
MimeTypeDetector::iconFor('video/mp4');     // "🎬"

// Регистрация кастомного MIME-типа
MimeTypeDetector::register('avif', 'image/avif');

// Список всех известных расширений/MIME-типов
MimeTypeDetector::allExtensions();
MimeTypeDetector::allMimeTypes();
```

### FileException

[`Pet\File\Exception\FileException`](File/Exception/FileException.php) — базовое исключение для всех файловых операций с фабричными методами.

```php
use Pet\File\Exception\FileException;

// Фабричные методы (HTTP-статус в коде исключения)
throw FileException::notFound($path);           // 404
throw FileException::notReadable($path);        // 403
throw FileException::notWritable($path);        // 403
throw FileException::uploadError();             // 400
throw FileException::invalidPath($path);        // 400
throw FileException::invalidImage();            // 400
throw FileException::directoryNotCreated($dir); // 500
throw FileException::saveError();               // 500
```

---

## Модули (Module)

В пространстве имён [`Pet\Module`](Module/) находятся готовые интеграции с внешними сервисами.

### PlusOfon — отправка SMS

[`Pet\Module\PlusOfon`](Module/PlusOfon.php) — клиент для отправки SMS через API сервиса PlusOfon.

```php
use Pet\Module\PlusOfon;

$sms = new PlusOfon('your-api-token');

$result = $sms->sms('+79161234567', 'Текст сообщения');

// Результат:
// [
//     'success' => true,
//     'id' => '12345',
// ]
//
// При ошибке:
// [
//     'success' => false,
//     'error' => 'PlusOfon: неверный номер получателя',
//     'status' => 400,
//     'details' => [...],
// ]
```

### Imap — работа с почтой

[`Pet\Module\Imap`](Module/Imap.php) — абстрактный класс для подключения к IMAP-серверам. Требует реализации метода [`loadVariable()`](Module/Imap.php:615) для получения настроек.

```php
use Pet\Module\Imap;

class MailHandler extends Imap
{
    public function loadVariable(string $name): string
    {
        // Вернуть значение по ключу (host, port, username, password, encryption, verify_ssl, folder)
        return $_ENV['imap.' . $name] ?? '';
    }
}

$mail = new MailHandler();

// Проверка конфигурации
$mail->isConfigured();
$mail->getMissingSettings();  // ['Хост (imap.host)', ...]
$mail->testConnection();

// Управление папками
$mail->getFolders();          // ['INBOX', 'Sent', ...]
$mail->ensureFolder('Archive');

// Получение писем
$mail->getMessages(50, 'ALL');              // последние 50 писем
$mail->getMessagesPaginated(0, 20, 'UNSEEN'); // непрочитанные с пагинацией
$mail->getMessage(123);                     // полное письмо с телом и вложениями

// Операции с письмами
$mail->markAsRead(123);
$mail->markAsUnread(123);
$mail->deleteMessage(123);
$mail->moveMessage(123, 'Archive');

// Вложения
$attachment = $mail->getAttachment(123, '1.2');
// ['success' => true, 'content' => '...']
```

Параметры конструктора [`Imap`](Module/Imap.php:22):

| Параметр | Тип | По умолчанию | Описание |
|----------------|---------|--------------|---------------------------|
| `$host` | `?string` | из `loadVariable()` | Хост IMAP-сервера |
| `$port` | `?int` | `993` | Порт |
| `$username` | `?string` | из `loadVariable()` | Логин |
| `$password` | `?string` | из `loadVariable()` | Пароль |
| `$encryption` | `?string` | `'ssl'` | Шифрование (`ssl`, `tls`, `none`) |
| `$verifySsl` | `?bool` | `true` | Проверка SSL-сертификата |
| `$folder` | `?string` | `'INBOX'` | Папка по умолчанию |

---

## Лицензия

MIT
