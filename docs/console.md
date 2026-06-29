# Консольные команды

## Стартовый файл

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

## Запуск команд

```bash
php pet [команда] [аргументы]
```

## Доступные команды

| Команда | Описание |
|---------|----------|
| [`serve`](../Command/Command.php:64) | Запускает встроенный PHP-сервер |
| [`migrate`](../Command/Command.php:44) | Выполняет миграции БД |
| [`socket`](../Command/Command.php:35) | Запускает WebSocket-сервер |
| [`make:model`](../Command/Command.php:47) | Создаёт новую модель через Blade-шаблон |
| [`list:model`](../Command/Command.php:50) | Выводит список всех моделей приложения |
| [`list:controller`](../Command/Command.php:52) | Выводит список всех контроллеров с экшенами |
| [`env`](../Command/Command.php:55) | Создаёт файл `.env` из шаблона |
| [`info`](../Command/Command.php:57) | Информация о фреймворке |
| [`git-monitor`](../Command/Command.php:60) | Мониторинг изменений в Git с автосборкой |
| [`git-update`](../Command/Command.php:63) | Pull изменений и сборка (однократно) |
| [`load`](../Command/Command.php:38) | Загрузка на сервер по FTP |
| [`load-diff`](../Command/Command.php:41) | Выгрузка только изменённых файлов |

## Console API

[`Pet\Command\Console\Console`](../Command/Console/Console.php) — утилита для цветного вывода, таблиц, прогресс-баров, гиперссылок и ввода.

### Цветной текст

```php
use Pet\Command\Console\Console;

Console::text('Hello World', 'green');
Console::text('Ошибка!', 'red', 'white'); // красный текст на белом фоне
```

Доступные цвета: `red`, `green`, `yellow`, `blue`, `black`, `white`, `violet`, `cyan`.

### Стилизованные сообщения

```php
Console::success('Операция выполнена');   // зелёный
Console::warning('Внимание!');            // жёлтый
Console::error('Критическая ошибка');     // красный
Console::info('Информация');              // синий
```

### Таблицы

```php
Console::table([
    ['Имя' => 'John', 'Возраст' => '30'],
    ['Имя' => 'Jane', 'Возраст' => '25'],
], ['Имя', 'Возраст'], 'cyan');
```

### Прогресс-бар

```php
Console::progressBar(5, 10, 50, 'Загрузка:');
// [====================>                     ] 50% (5/10)
```

### Гиперссылки (OSC 8)

Кликабельные ссылки в терминале. Поддерживаются в VS Code, GNOME Terminal, iTerm2, kitty, Windows Terminal, Konsole.

```php
Console::link('Нажми меня', 'https://example.com', 'green');
Console::link('PET Framework', 'https://github.com/AlexNextProgramm/pet-sample-1', 'yellow');
```

### Ввод и подтверждения

```php
$name = Console::ask('Введите имя', 'Гость');
if (Console::confirm('Продолжить?')) {
    // ...
}
```

### Заголовки и списки

```php
Console::header('Мой заголовок', 'yellow', 60);
// ============================================================
//                      Мой заголовок
// ============================================================

Console::bulletList(['пункт 1', 'пункт 2'], 'green');
Console::numberedList(['шаг 1', 'шаг 2'], 'white');
```

### Прочие методы

```php
Console::clear();           // Очистка консоли
Console::newLine(2);        // Пустые строки
Console::wait('Нажмите Enter...'); // Ожидание клавиши
Console::die('Ошибка', 'red'); // Вывод и завершение
Console::print($data);      // print_r с цветом
Console::log('Текст');      // Алиас для text()
Console::cmd('ls -la', $callback); // Выполнение команды с callback
Console::system('ls -la');  // Выполнение system()
```

## Генерация модели

```bash
php pet make:model User
```

Создаёт файл `app/Model/User.php` из Blade-шаблона [`blade/Model.blade.php`](../blade/Model.blade.php). Модель включает:

- Пространство имён `App\Model`
- Свойство `$table` (автоматически из имени: User → users)
- Свойство `$hidden` с `password`
- Методы `all()`, `find()`, `create()`, `update()`, `delete()`

## Список моделей

```bash
php pet list:model
```

Выводит таблицу со всеми моделями приложения, их таблицами БД и файлами.

## Список контроллеров

```bash
php pet list:controller
```

Выводит таблицу со всеми контроллерами, их экшенами (публичными методами) и файлами. Экшены извлекаются парсингом файла без загрузки класса.