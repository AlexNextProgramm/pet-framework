# Работа с запросами

Класс [`Pet\Request\Request`](../Request/Request.php) предоставляет доступ к данным HTTP-запроса.

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

## Вспомогательные функции

- [`attr()`](../chain/chain.php:24) — получить значение input
- [`attrs()`](../chain/chain.php:33) — получить все входные данные
- [`request()`](../chain/chain.php:42) — получить объект Request
- [`files()`](../chain/chain.php:53) — получить файл
- [`supple()`](../chain/chain.php:64) — получить параметр из URL
- [`levels()`](../chain/chain.php:78) — сегменты пути
- [`original()`](../chain/chain.php:88) — доменное имя