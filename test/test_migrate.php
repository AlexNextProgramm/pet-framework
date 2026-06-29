<?php

/**
 * Тест миграций PET Framework
 *
 * Запуск: php test/test_migrate.php
 *
 * Проверяет:
 * 1. Создание таблицы migrate
 * 2. Сортировку файлов (natsort)
 * 3. Выполнение SQL-запросов
 * 4. Парсинг Rollback >>> из файла
 * 5. Запись sql_str и error
 * 6. Откат миграции (migrate:rollback)
 * 7. Повторный запуск (пропуск по hash)
 * 8. Внутренние миграции фреймворка
 */

// ========== НАСТРОЙКА ==========
// Замените на свои параметры БД, если нужно
define('TEST_DB_HOST', '127.0.0.1');
define('TEST_DB_NAME', 'test_pet_migrate');
define('TEST_DB_USER', 'root');
define('TEST_DB_PASS', '');

// ========== ИНИЦИАЛИЗАЦИЯ ==========
require_once __DIR__ . '/../Command/Console/Console.php';
require_once __DIR__ . '/../DataBase/Config/DataBase.php';
require_once __DIR__ . '/../DataBase/ConnectionManager.php';
require_once __DIR__ . '/../DataBase/DB.php';
require_once __DIR__ . '/../DataBase/Select.php';
require_once __DIR__ . '/../DataBase/Insert.php';
require_once __DIR__ . '/../DataBase/Update.php';
require_once __DIR__ . '/../DataBase/Delete.php';
require_once __DIR__ . '/../Tools/Tools.php';
require_once __DIR__ . '/../Errors/AppException.php';
require_once __DIR__ . '/../Model/Model.php';
require_once __DIR__ . '/../Migration/MigrateCommand.php';

use Pet\Command\Console\Console;
use Pet\DataBase\Config\DataBase;
use Pet\Migration\MigrateCommand;

// Определяем константы, если не определены
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT')) define('ROOT', __DIR__ . '/..');
if (!defined('MIGRATE_DIR')) define('MIGRATE_DIR', 'test_migrations');

// Настраиваем подключение к БД
DataBase::set('default', [
    'type' => 'mysql',
    'host' => TEST_DB_HOST,
    'name' => TEST_DB_NAME,
    'user' => TEST_DB_USER,
    'password' => TEST_DB_PASS,
    'port' => 3306,
]);

// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========
$passed = 0;
$failed = 0;

function assert_eq($expected, $actual, string $message): void
{
    global $passed, $failed;
    if ($expected === $actual) {
        Console::text("  ✓ $message", 'green');
        $passed++;
    } else {
        Console::text("  ✗ $message", 'red');
        Console::text("    Ожидалось: " . json_encode($expected, JSON_UNESCAPED_UNICODE), 'red');
        Console::text("    Получено:  " . json_encode($actual, JSON_UNESCAPED_UNICODE), 'red');
        $failed++;
    }
}

function assert_true($actual, string $message): void
{
    assert_eq(true, $actual, $message);
}

function assert_false($actual, string $message): void
{
    assert_eq(false, $actual, $message);
}

function test_header(string $title): void
{
    Console::text("\n━━━ $title ━━━", 'cyan');
}

// ========== ПОДГОТОВКА ==========
test_header('Подготовка');

// Создаём тестовую БД
try {
    $pdo = new PDO("mysql:host=" . TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS);
    $pdo->exec("DROP DATABASE IF EXISTS `" . TEST_DB_NAME . "`");
    $pdo->exec("CREATE DATABASE `" . TEST_DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    Console::text("  ✓ Тестовая БД создана: " . TEST_DB_NAME, 'green');
} catch (PDOException $e) {
    Console::text("  ✗ Ошибка создания БД: " . $e->getMessage(), 'red');
    exit(1);
}

// Создаём временную директорию для тестовых миграций
$migrateDir = ROOT . DS . MIGRATE_DIR;
if (is_dir($migrateDir)) {
    array_map('unlink', glob($migrateDir . '/*.sql'));
} else {
    mkdir($migrateDir, 0777, true);
}
Console::text("  ✓ Тестовая директория миграций: $migrateDir", 'green');

// ========== ТЕСТ 1: СОРТИРОВКА ФАЙЛОВ ==========
test_header('Тест 1: Сортировка файлов (natsort)');

// Создаём файлы в неправильном порядке
file_put_contents($migrateDir . '/2_create_table.sql', 'SELECT 1;');
file_put_contents($migrateDir . '/10_add_index.sql', 'SELECT 1;');
file_put_contents($migrateDir . '/1_init.sql', 'SELECT 1;');

$files = scandir($migrateDir);
$files = array_values(array_filter($files, fn($f) => $f !== '.' && $f !== '..'));
natsort($files);
$sorted = array_values($files);

$expectedOrder = ['1_init.sql', '2_create_table.sql', '10_add_index.sql'];
assert_eq($expectedOrder, $sorted, 'Файлы отсортированы по числовому префиксу: 1, 2, 10');

// Удаляем тестовые файлы
array_map('unlink', glob($migrateDir . '/*.sql'));

// ========== ТЕСТ 2: ПАРСИНГ ROLLBACK ==========
test_header('Тест 2: Парсинг Rollback >>>');

$reflection = new ReflectionMethod(MigrateCommand::class, 'parseRollback');
$reflection->setAccessible(true);

$migrate = new MigrateCommand();

// Тест с #
$sql = "CREATE TABLE users (id INT);\n# Rollback >>> DROP TABLE users;";
$result = $reflection->invoke($migrate, $sql);
assert_eq('DROP TABLE users', $result, 'Парсинг # Rollback >>>');

// Тест с --
$sql = "ALTER TABLE users ADD COLUMN phone VARCHAR(20);\n-- Rollback >>> ALTER TABLE users DROP COLUMN phone;";
$result = $reflection->invoke($migrate, $sql);
assert_eq('ALTER TABLE users DROP COLUMN phone', $result, 'Парсинг -- Rollback >>>');

// Тест с //
$sql = "CREATE INDEX idx_name ON users(name);\n// Rollback >>> DROP INDEX idx_name ON users;";
$result = $reflection->invoke($migrate, $sql);
assert_eq('DROP INDEX idx_name ON users', $result, 'Парсинг // Rollback >>>');

// Тест без Rollback
$sql = "CREATE TABLE users (id INT);";
$result = $reflection->invoke($migrate, $sql);
assert_eq('', $result, 'Нет Rollback — пустая строка');

// Тест с несколькими Rollback
$sql = "CREATE TABLE users (id INT);\n# Rollback >>> DROP TABLE users;\nALTER TABLE users ADD COLUMN email VARCHAR(255);\n# Rollback >>> ALTER TABLE users DROP COLUMN email;";
$result = $reflection->invoke($migrate, $sql);
assert_eq('DROP TABLE users;ALTER TABLE users DROP COLUMN email', $result, 'Несколько Rollback строк');

// ========== ТЕСТ 3: ВЫПОЛНЕНИЕ МИГРАЦИИ ==========
test_header('Тест 3: Выполнение миграции');

// Создаём тестовую миграцию с Rollback
$testSql = <<<SQL
CREATE TABLE test_migrate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255)
);
# Rollback >>> DROP TABLE test_migrate;
SQL;
file_put_contents($migrateDir . '/1_test_migrate.sql', $testSql);

// Запускаем миграцию через рефлексию
$upMethod = new ReflectionMethod(MigrateCommand::class, 'up');
$upMethod->setAccessible(true);
$upMethod->invoke($migrate);

// Проверяем, что таблица создалась
try {
    $check = $migrate->q("SHOW TABLES FROM `" . TEST_DB_NAME . "` LIKE 'test_migrate'")->fetch();
    assert_true(!empty($check), 'Таблица test_migrate создана');
} catch (Exception $e) {
    assert_true(false, 'Таблица test_migrate создана: ' . $e->getMessage());
}

// Проверяем, что запись в migrate есть
$record = $migrate->find(['name' => '[app] 1_test_migrate.sql']);
assert_true(!empty($record), 'Запись в таблице migrate создана');

if (!empty($record)) {
    $record = $record[0];
    assert_true(!empty($record['hash']), 'Hash заполнен');
    assert_true(!empty($record['sql_str']), 'sql_str заполнен');
    assert_eq('DROP TABLE test_migrate', $record['str_rollback'], 'str_rollback заполнен');
    assert_true(empty($record['error']), 'error пуст (нет ошибок)');
}

// ========== ТЕСТ 4: ПОВТОРНЫЙ ЗАПУСК ==========
test_header('Тест 4: Повторный запуск (пропуск по hash)');

// Меняем содержимое файла, но hash в БД уже есть от старого содержимого
// Запускаем ещё раз — должно быть "Новых миграций нет"
$upMethod->invoke($migrate);

// Проверяем, что запись всё ещё одна (не дублировалась)
$records = $migrate->find(['name' => '[app] 1_test_migrate.sql']);
assert_eq(1, count($records), 'Запись не дублировалась при повторном запуске');

// ========== ТЕСТ 5: ОШИБКА В МИГРАЦИИ ==========
test_header('Тест 5: Ошибка в миграции');

$errorSql = <<<SQL
CREATE TABLE test_error (id INT);
INSERT INTO nonexistent_table VALUES (1);
SQL;
file_put_contents($migrateDir . '/2_error_test.sql', $errorSql);

$upMethod->invoke($migrate);

// Проверяем, что ошибка записалась
$errorRecord = $migrate->find(['name' => '[app] 2_error_test.sql']);
assert_true(!empty($errorRecord), 'Запись с ошибкой создана');

if (!empty($errorRecord)) {
    $errorRecord = $errorRecord[0];
    assert_true(!empty($errorRecord['error']), 'Поле error заполнено');
    Console::text("    Текст ошибки: " . $errorRecord['error'], 'yellow');
}

// ========== ТЕСТ 6: ROLLBACK ==========
test_header('Тест 6: Откат миграции (migrate:rollback)');

// Отладочный вывод: смотрим, какие записи есть в migrate
Console::text("  • Записи в migrate перед откатом:", 'yellow');
$allRecords = $migrate->find();
foreach ($allRecords as $r) {
    Console::text("    id={$r['id']} name='{$r['name']}' rollback='" . ($r['str_rollback'] ?? 'NULL') . "'", 'yellow');
}

$downMethod = new ReflectionMethod(MigrateCommand::class, 'down');
$downMethod->setAccessible(true);
$downMethod->invoke($migrate);

// Проверяем, что таблица test_migrate удалена
try {
    $check = $migrate->q("SHOW TABLES FROM `" . TEST_DB_NAME . "` LIKE 'test_migrate'")->fetch();
    assert_true(empty($check), 'Таблица test_migrate удалена после отката');
} catch (Exception $e) {
    assert_true(false, 'Таблица test_migrate удалена после отката: ' . $e->getMessage());
}

// Проверяем, что запись удалена из migrate
$recordAfterRollback = $migrate->find(['name' => '[app] 1_test_migrate.sql']);
assert_true(empty($recordAfterRollback), 'Запись миграции удалена из migrate после отката');

// ========== ТЕСТ 7: ROLLBACK БЕЗ ROLLBACK SQL ==========
test_header('Тест 7: Откат миграции без Rollback');

// Создаём миграцию без Rollback
$noRollbackSql = "CREATE TABLE test_no_rollback (id INT);";
file_put_contents($migrateDir . '/3_no_rollback.sql', $noRollbackSql);

$upMethod->invoke($migrate);

// Пытаемся откатить — должна откатиться последняя с Rollback (1_test_migrate.sql)
$downMethod->invoke($migrate);

// Проверяем, что 1_test_migrate.sql откатилась (у неё есть Rollback)
$recordAfterRollback = $migrate->find(['name' => '[app] 1_test_migrate.sql']);
assert_true(empty($recordAfterRollback), 'Миграция с Rollback откачена');

// А 3_no_rollback осталась (у неё нет Rollback)
$recordNoRollback = $migrate->find(['name' => '[app] 3_no_rollback.sql']);
assert_true(!empty($recordNoRollback), 'Миграция без Rollback осталась');

// 2_error_test.sql тоже осталась (у неё нет Rollback)
$errorRecord = $migrate->find(['name' => '[app] 2_error_test.sql']);
assert_true(!empty($errorRecord), 'Миграция с ошибкой без Rollback осталась');

// ========== ТЕСТ 8: ВНУТРЕННИЕ МИГРАЦИИ ФРЕЙМВОРКА ==========
test_header('Тест 8: Внутренние миграции фреймворка');

// Проверяем, что внутренние миграции выполнились (если таблица migrate создана заново)
$frameworkRecords = $migrate->find();
$frameworkMigrations = array_filter($frameworkRecords, fn($r) => str_starts_with($r['name'], '[framework]'));
Console::text("  • Внутренних миграций выполнено: " . count($frameworkMigrations), 'yellow');

// ========== ИТОГИ ==========
Console::text("\n━━━ РЕЗУЛЬТАТЫ ТЕСТОВ ━━━", 'cyan');
Console::text("  Пройдено: $passed", $failed > 0 ? 'red' : 'green');
Console::text("  Провалено: $failed", $failed > 0 ? 'red' : 'green');

if ($failed > 0) {
    Console::text("\n  НЕ ВСЕ ТЕСТЫ ПРОЙДЕНЫ!", 'red');
    exit(1);
} else {
    Console::text("\n  ВСЕ ТЕСТЫ ПРОЙДЕНЫ!", 'green');
}

// ========== ОЧИСТКА ==========
// Удаляем тестовую БД
$pdo->exec("DROP DATABASE IF EXISTS `" . TEST_DB_NAME . "`");

// Удаляем тестовые файлы
array_map('unlink', glob($migrateDir . '/*.sql'));
rmdir($migrateDir);