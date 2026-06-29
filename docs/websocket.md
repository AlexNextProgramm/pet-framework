# WebSocket

Фреймворк поддерживает нативные WebSocket-серверы.

## Создание сокет-сервера

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

## Запуск

```bash
php pet socket chat