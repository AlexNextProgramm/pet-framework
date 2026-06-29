# Вспомогательные функции

## Глобальные функции

### Из function.php

| Функция | Назначение |
|---------|-----------|
| [`dd()`](../function.php:85) | Дамп переменных с завершением |
| [`env()`](../function.php:21) | Получение значения из .env |
| [`svg()`](../function.php:95) | Вставка SVG |
| [`img()`](../function.php:106) | Путь к изображению |
| [`uploads()`](../function.php:113) | Путь к загруженному файлу |
| [`dirEach()`](../function.php:134) | Итерация по директории |

### Из chain/chain.php

| Функция | Назначение |
|---------|-----------|
| [`view()`](../chain/chain.php:17) | Отображение шаблона (PHP или Blade) |
| [`blade()`](../chain/chain.php:107) | Рендеринг Blade-шаблона с возвратом HTML |
| [`attr()`](../chain/chain.php:27) | Получение input-данных по имени |
| [`attrs()`](../chain/chain.php:36) | Все входные данные |
| [`request()`](../chain/chain.php:45) | Объект Request |
| [`files()`](../chain/chain.php:57) | Загруженный файл (File или FileCollection) |
| [`supple()`](../chain/chain.php:68) | Параметр из URL (`{param}`) |
| [`levels()`](../chain/chain.php:82) | Сегменты пути (`/a/b/c` → `['a', 'b', 'c']`) |
| [`original()`](../chain/chain.php:92) | Доменное имя запроса |

### blade()

Рендеринг Blade-шаблона с возвратом HTML.

```php
$html = blade('user.profile', ['name' => 'John']);
// Аналог: Blade::render('user.profile', ['name' => 'John'])
```

### view()

Отображение шаблона. Поддерживает как `.php`, так и `.blade.php` файлы.

```php
view('user.profile', ['name' => 'John']);
// Если есть view/user/profile.blade.php — использует Blade
// Если есть view/user/profile.php — использует обычный PHP
```

## Tools

| Метод | Назначение |
|-------|-----------|
| [`Tools::jsonDe()`](../Tools/Tools.php:17) | Безопасный JSON decode |
| [`Tools::array_implode()`](../Tools/Tools.php:52) | Имплод ассоциативного массива |
| [`Tools::is_assos()`](../Tools/Tools.php:67) | Определение типа массива |
| [`Tools::strRepalceFile()`](../Tools/Tools.php:92) | Замена в файле |
| [`Tools::filter()`](../Tools/Tools.php:101) | Фильтрация массива с ключами |
| [`Tools::scan()`](../Tools/Tools.php:109) | Сканирование директории |