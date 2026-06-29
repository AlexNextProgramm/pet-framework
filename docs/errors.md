# Обработка ошибок

## Класс Errors

[`Pet\Errors\Errors`](../Errors/Errors.php) автоматически логирует фатальные ошибки в файл, указанный в константе `LOG`.

## Исключения

```php
use Pet\Errors\AppException;

throw new AppException('Сообщение об ошибке', E_ERROR);
```

## HTTP-ошибки

```php
use Pet\Router\Error;

// Установка кастомного обработчика
Error::$events[404] = [NotFoundController::class, 'handle'];

// Вызов
Error::setHttp(404, 'Страница не найдена');
```

## Ответы

Класс [`Pet\Router\Response`](../Router/Response.php) для формирования HTTP-ответов.

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