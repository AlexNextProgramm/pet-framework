# Cookie и Сессии

## Cookie

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

## Сессии

```php
use Pet\Session\Session;

// Установка
Session::set(['user_id' => 42]);

// Получение
$userId = Session::get('user_id');
$all    = Session::get(); // все данные

// Уничтожение
Session::kill();