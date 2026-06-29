# Middleware

Middleware создаётся наследованием от [`Pet\Router\Middleware`](../Router/Middleware.php).

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