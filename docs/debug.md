# Отладка (DebugBar)

**PET Framework** предоставляет встроенную отладочную панель **DebugBar**, которая отображается внизу страницы и собирает информацию о выполнении приложения: SQL-запросы, время выполнения, использование памяти и список подключённых файлов.

---

## Включение DebugBar

DebugBar включается через статическое свойство [`App::$debug`](../App.php:21) перед вызовом `App::init()`:

```php
use Pet\App;

App::$debug = true;
App::init();
```

При `App::$debug = true`:
- Определяется константа `PET_DEBUG`
- Засекается время старта приложения
- Включается сбор SQL-запросов в [`DB`](../DataBase/DB.php)
- В [`View`](../View/View.php) автоматически вставляется HTML-код панели перед `</body>`

При `App::$debug = false` (по умолчанию) DebugBar полностью отключён и не влияет на производительность.

---

## Компоненты DebugBar

### 1. [`Debug/DebugBar.php`](../Debug/DebugBar.php) — класс-коллектор

Класс `Pet\Debug\DebugBar` собирает и отображает отладочные данные.

| Метод | Описание |
|---|---|
| `start()` | Засекает время старта (`microtime(true)`) |
| `stop()` | Засекает время финиша |
| `addQuery(string $query, float $time)` | Добавляет SQL-запрос в лог |
| `getQueries()` | Возвращает массив SQL-запросов |
| `getExecutionTime()` | Возвращает общее время выполнения в секундах |
| `getMemoryUsage()` | Возвращает пик памяти (B, KB, MB, GB) |
| `getIncludedFiles()` | Возвращает список подключённых файлов |
| `reset()` | Очищает все собранные данные |
| `render()` | Возвращает HTML-код панели |

### 2. [`Debug/style.php`](../Debug/style.php) — CSS-стили

Тёмная тема в стиле Catppuccin. Подключается через `include` в `render()`.

### 3. [`Debug/script.php`](../Debug/script.php) — JavaScript

Управление вкладками и сворачивание панели. Функции:
- `switchDebugTab(tabName)` — переключение между вкладками
- `toggleDebugBar()` — свернуть/развернуть панель
- `toggleVendorFiles()` — показать/скрыть vendor-файлы

---

## Вкладки панели

### ⚡ Скорость

Отображает метрики производительности:

| Метрика | Описание |
|---|---|
| **Время выполнения** | От `start()` до `stop()` в секундах |
| **Пик памяти** | `memory_get_peak_usage(true)` |
| **SQL запросов** | Количество выполненных запросов |
| **Подключено файлов** | Количество файлов приложения (без vendor) |

### 🗄️ SQL

Список всех SQL-запросов, выполненных через [`DB`](../DataBase/DB.php):
- Номер запроса
- Текст SQL-запроса (с подсветкой синтаксиса)
- Время выполнения в секундах

Если запросов не было — отображается сообщение "SQL-запросы не выполнялись."

### 📁 Файлы

Список всех подключённых PHP-файлов (`get_included_files()`):

- **Файлы приложения** — отображаются по умолчанию (все пути, не содержащие `/vendor/`)
- **Vendor-файлы** — скрыты по умолчанию, отображаются после установки чекбокса "Показать vendor-файлы"

Бейдж на вкладке показывает количество файлов приложения (без vendor).

---

## Интеграция с компонентами

### [`App.php`](../App.php)

```php
public static bool $debug = false;

public static function init()
{
    if (self::$debug) {
        defined('PET_DEBUG') || define('PET_DEBUG', true);
        DebugBar::start();
    } else {
        defined('PET_DEBUG') || define('PET_DEBUG', false);
    }
    // ...
}
```

### [`DataBase/DB.php`](../DataBase/DB.php)

Метод `logQuery()` вызывается в:
- `fetch()` — после выполнения SELECT
- `execute()` — после выполнения INSERT/UPDATE/DELETE
- `q()` — после выполнения произвольного SQL

```php
protected function logQuery(string $query, float $start): void
{
    if (defined('PET_DEBUG') && PET_DEBUG === true) {
        $time = microtime(true) - $start;
        DebugBar::addQuery($query, $time);
    }
}
```

### [`View/View.php`](../View/View.php)

Метод `injectDebugBar()` вставляет HTML панели перед `</body>`:

```php
private static function injectDebugBar(string $html): string
{
    if (!defined('PET_DEBUG') || PET_DEBUG !== true) {
        return $html;
    }

    DebugBar::stop();
    $debugHtml = DebugBar::render();

    $pos = strripos($html, '</body>');
    if ($pos !== false) {
        return substr_replace($html, $debugHtml . "\n", $pos, 0);
    }

    return $html . "\n" . $debugHtml;
}
```

Поддерживаются оба типа шаблонов:
- **PHP-шаблоны** (`.php`) — буферизация вывода через `ob_start()`/`ob_get_clean()`
- **Blade-шаблоны** (`.blade.php`) — результат `Blade::render()` проходит через `injectDebugBar()`

---

## Навигация по коду для AI-агентов

| Что искать | Где |
|---|---|
| **Класс DebugBar** | [`Debug/DebugBar.php`](../Debug/DebugBar.php) |
| **CSS-стили** | [`Debug/style.php`](../Debug/style.php) |
| **JavaScript** | [`Debug/script.php`](../Debug/script.php) |
| **Включение отладки** | [`App.php:21`](../App.php:21) (`App::$debug`) |
| **Сбор SQL-запросов** | [`DataBase/DB.php:382`](../DataBase/DB.php:382) (`logQuery()`) |
| **Инъекция в шаблоны** | [`View/View.php:194`](../View/View.php:194) (`injectDebugBar()`) |

### Ключевые фразы для поиска

| Фраза | Что ищет |
|---|---|
| `PET_DEBUG` | Проверка включения отладки |
| `DebugBar::` | Вызовы методов DebugBar |
| `injectDebugBar` | Инъекция панели в HTML |
| `logQuery` | Логирование SQL-запросов |
| `pet-debug-bar` | CSS-класс панели |
| `switchDebugTab` | JS-функция переключения вкладок |

---

## Пример использования

```php
// public_html/index.php
use Pet\App;

// Включаем отладочную панель
App::$debug = true;

// Запускаем приложение
App::init();
```

После этого на всех страницах, отображаемых через [`View::open()`](../View/View.php) или [`View::getTemplate()`](../View/View.php), внизу будет отображаться отладочная панель.