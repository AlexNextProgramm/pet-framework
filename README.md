# PET Framework (для проектов)

**PET** — легковесный PHP-фреймворк для веб-приложений с поддержкой маршрутизации, ORM, WebSocket, миграций, консольных команд и гибкой системы шаблонов.

---

## Содержание

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Конфигурация](#конфигурация)
- [Структура проекта](#структура-проекта)
- [Маршрутизация](#маршрутизация)
- [Контроллеры](#контроллеры)
- [Модели и ORM](#модели-и-orm)
- [Шаблоны (View)](#шаблоны-view)
- [Middleware](#middleware)
- [WebSocket](#websocket)
- [Миграции](#миграции)
- [Консольные команды](#консольные-команды)
- [Работа с запросами](#работа-с-запросами)
- [Cookie и Сессии](#cookie-и-сессии)
- [Обработка ошибок](#обработка-ошибок)
- [Вспомогательные функции](#вспомогательные-функции)
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

Создайте файл `.env` в корне проекта:

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
├── Frontend/         # Фронтенд-ресурсы
├── Git/              # Git-мониторинг
├── Migration/        # Миграции БД
├── Model/            # Базовый класс модели
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

Модели наследуются от [`Pet\Model\Model`](Model/Model.php) и используют traits для операций CRUD.

### Определение модели

```php
use Pet\Model\Model;

class User extends Model
{
    protected string $table = 'users';
    public array $hidden = ['password']; // скрытые поля
}
```

### Основные операции

```php
// Поиск
$users = (new User())->find(['role' => 'admin']);
$user  = (new User(1)); // по ID

// Создание
$user = new User(['name' => 'John', 'email' => 'john@example.com'], true);

// Обновление
$user->set('name', 'Jane');
// или
$user->set(['name' => 'Jane', 'email' => 'jane@example.com']);

// Удаление
$user->delete();

// Массовое удаление
$user->findDelete(['role' => 'guest']);
```

### Построитель запросов

```php
$users = (new User())
    ->select('id', 'name')
    ->where('role = ?', ['admin'])
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->offset(0)
    ->fetch();
```

### Методы выборки

- [`find()`](Model/Model.php:56) — возвращает массив результатов
- [`findM()`](Model/Model.php:78) — возвращает массив моделей
- [`exist()`](Model/Model.php:141) — проверяет существование
- [`max()`](DataBase/Select.php:235) — максимальное значение поля
- [`data()`](Model/Model.php:205) — возвращает данные с учётом скрытых полей

### Join

```php
$posts = (new Post())
    ->select('posts.*', 'users.name as author')
    ->join('users')
    ->on(['posts.user_id', 'users.id', '='])
    ->fetch();
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

Запуск команд:

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

## Лицензия

MIT
