# Миграции

Миграции — это SQL-файлы в директории `migrate/`.

## Создание миграции

Создайте SQL-файл в `migrate/`, например `001_create_users.sql`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Запуск миграций

```bash
php pet migrate
```

Фреймворк отслеживает выполненные миграции по хешу содержимого файла. Повторно не выполняет.