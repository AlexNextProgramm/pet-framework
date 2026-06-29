# Маршрутизация

Маршруты определяются в файле `routes.php` вашего приложения.

## Простые маршруты

```php
use Pet\Router\Router;

Router::get('/', function() {
    return 'Hello, World!';
});

Router::post('/user', [UserController::class, 'store']);
```

## Поддерживаемые HTTP-методы

- [`Router::get()`](../Router/Router.php:22) — GET-запросы
- [`Router::post()`](../Router/Router.php:28) — POST-запросы
- [`Router::put()`](../Router/Router.php:40) — PUT-запросы
- [`Router::delete()`](../Router/Router.php:34) — DELETE-запросы
- [`Router::options()`](../Router/Router.php:46) — OPTIONS-запросы

## Гибкие URL (параметры в пути)

```php
Router::get('/user/{id}', [UserController::class, 'show']);
// /user/42 → $id = 42
```

Параметры извлекаются через функцию [`supple()`](../chain/chain.php:64):

```php
function show() {
    $id = supple('id'); // 42
}
```

## Wildcard маршруты

```php
Router::get('/admin/*', [AdminController::class, 'handle']);
```

## Именованные маршруты и группы

```php
Router::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->group('admin');
```

## Middleware

```php
use Pet\Router\Middleware;

Middleware::middleware(AuthMiddleware::class)
    ->get('/profile', [ProfileController::class, 'index'])
    ->post('/profile', [ProfileController::class, 'update']);