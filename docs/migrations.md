# Миграции

Миграции — это SQL-файлы в директории `migrate/`. PET Framework выполняет их последовательно, отслеживает по хешу и поддерживает откат.

## Создание миграции

Создайте SQL-файл в `migrate/`, например `1_create_users.sql`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Файлы нумеруются по порядку: `1_...sql`, `2_...sql`, `3_...sql` и т.д. Сортировка выполняется по числовому префиксу (натуральная сортировка, `natsort`), поэтому `2_...` будет перед `10_...`.

## Запуск миграций

```bash
php pet migrate
```

Фреймворк отслеживает выполненные миграции по MD5-хешу содержимого файла. Если файл не менялся — повторно не выполняет.

Порядок выполнения:
1. Сначала **внутренние миграции фреймворка** (из `vendor/pet/framework/Migration/migration/`) — помечаются `[framework]`
2. Затем **пользовательские миграции** (из `migrate/`) — помечаются `[app]`

## Rollback (откат миграции)

### Как указать Rollback в файле миграции

В файле миграции можно указать SQL-запрос для отката с помощью комментария:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE
);
# Rollback >>> DROP TABLE users;
```

Поддерживаются форматы комментариев:
- `# Rollback >>> ...`
- `-- Rollback >>> ...`
- `// Rollback >>> ...`

Можно указать **несколько строк Rollback** — каждая на отдельной строке:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255)
);
# Rollback >>> DROP TABLE users;

ALTER TABLE users ADD COLUMN phone VARCHAR(20);
# Rollback >>> ALTER TABLE users DROP COLUMN phone;
```

Rollback-запросы сохраняются в поле `str_rollback` таблицы `migrate` и **не выполняются** при обычном запуске миграции.

### Команда отката

```bash
php pet migrate:rollback
# или
php pet migrate:down
```

Команда находит **последнюю** миграцию, у которой заполнено поле `str_rollback`, выполняет указанные в нём SQL-запросы и удаляет запись из таблицы `migrate`.

**Важно:** Если в файле миграции не указан `# Rollback >>> ...`, то откатить эту миграцию будет невозможно — команда просто перейдёт к предыдущей миграции, у которой есть Rollback.

### Пример работы с Rollback

1. Создаём файл `1_create_users.sql`:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255)
);
# Rollback >>> DROP TABLE users;
```

2. Запускаем миграцию:
```bash
php pet migrate
# Вывод:
# 1) [app] Выполнен: CREATE TABLE users (...
```

3. Если нужно откатить:
```bash
php pet migrate:rollback
# Вывод:
# Откат миграции: [app] 1_create_users.sql
# 1) Откат: DROP TABLE users;
# Миграция [app] 1_create_users.sql откачена
```

4. Повторный запуск миграции выполнит её снова (запись удалена):
```bash
php pet migrate
# 1) [app] Выполнен: CREATE TABLE users (...
```

## Обработка ошибок

Если при выполнении SQL-запроса произошла ошибка:
- Ошибка записывается в поле `error` таблицы `migrate`
- Сам запрос, на котором произошла ошибка, записывается в поле `sql_str`
- Выполнение миграции **прерывается** (остальные запросы в файле не выполняются)

Посмотреть ошибки можно напрямую в БД:

```sql
SELECT * FROM `migrate` WHERE `error` IS NOT NULL;
```

## Внутренние миграции фреймворка

При запуске `php pet migrate` сначала выполняются внутренние миграции самого PET Framework (из `vendor/pet/framework/Migration/migration/`), а затем — пользовательские миграции из `migrate/`.

Перед выполнением миграций команда автоматически создаёт таблицу `migrate` (если её нет) и добавляет недостающие колонки `str_rollback` и `error` в уже существующую таблицу со старой схемой.

Внутренние миграции помечаются меткой `[framework]` в поле `name` таблицы `migrate`. Они также поддерживают Rollback.

Текущие внутренние миграции:
- `1_add_str_rollback.sql` — добавляет колонку `str_rollback` в таблицу `migrate` (если её нет)
- `2_add_error.sql` — добавляет колонку `error` в таблицу `migrate` (если её нет)

Внутренние миграции используют динамический SQL с проверкой существования колонок через `information_schema.COLUMNS`, поэтому безопасны для повторного выполнения.

## Структура таблицы migrate

```sql
CREATE TABLE `migrate` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(500) NULL DEFAULT NULL,
    `hash` VARCHAR(500) NULL DEFAULT NULL,
    `cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sql_str` TEXT NULL DEFAULT NULL,
    `str_rollback` TEXT NULL DEFAULT NULL,
    `error` TEXT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
```

| Поле | Тип | Описание |
|---|---|---|
| `id` | INT (AUTO_INCREMENT) | Автоинкрементный идентификатор |
| `name` | VARCHAR(500) | Имя файла миграции с меткой: `[framework]` или `[app]` |
| `hash` | VARCHAR(500) | MD5-хеш содержимого файла (для отслеживания повторного выполнения) |
| `cdate` | DATETIME | Дата и время выполнения |
| `sql_str` | TEXT | Выполненные SQL-запросы (через `;`) |
| `str_rollback` | TEXT | SQL-запросы для отката (из `# Rollback >>>`) |
| `error` | TEXT | Текст ошибки, если выполнение провалилось |

## Список команд

| Команда | Описание |
|---|---|
| `php pet migrate` | Выполнить все новые миграции |
| `php pet migrate:rollback` | Откатить последнюю миграцию с Rollback |
| `php pet migrate:down` | Синоним `migrate:rollback` |