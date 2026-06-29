<?php

namespace Pet\Migration;

use Error;
use Exception;
use Pet\Command\Console\Console;
use Pet\Model\Model;

class MigrateCommand extends Model
{

    private $DIR = '';
    private $FRAMEWORK_MIGRATE_DIR = '';
    public $hash = '';
    public $command = '';
    public $isParam = false;
    public $param = false;
    public $endFile = '';
    protected string $table = 'migrate';

    public function __construct() {
        $this->DIR = ROOT . DS . MIGRATE_DIR;
        if (!is_dir($this->DIR)) {
            mkdir($this->DIR, 0777);
        }
        $this->FRAMEWORK_MIGRATE_DIR = __DIR__ . DS . 'migration';
        parent::__construct();
    }

    public static function init($command)
    {
        $migrate = new self();
        switch ($command) {
            case 'migrate':
                $migrate->up();
                break;
            case 'migrate:rollback':
            case 'migrate:down':
                $migrate->down();
                break;
        }
    }

    /**
     * Создаёт таблицу migrate, если её нет.
     */
    private function ensureMigrateTable(): void
    {
        $table = $this->q("SHOW TABLES FROM `".$this->db_name."` LIKE 'migrate' ; ")->fetch();
        if (empty($table)) {
            $this->q(
                "CREATE TABLE `migrate` (
                    `id` INT NOT NULL AUTO_INCREMENT ,
                    `name` VARCHAR(500) NULL DEFAULT NULL ,
                    `hash`  VARCHAR(500) NULL DEFAULT NULL ,
                    `cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `sql_str`  TEXT NULL DEFAULT NULL,
                    `str_rollback` TEXT NULL DEFAULT NULL,
                    `error` TEXT NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE = InnoDB;"
            );
        }
    }

    /**
     * Парсит файл миграции, извлекая Rollback-запрос.
     * Формат: # Rollback >>> DROP TABLE users;
     * Может быть несколько строк.
     */
    private function parseRollback(string $content): string
    {
        $parts = [];
        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^\s*(#|--|\/\/)\s*Rollback\s*>>>\s*(.*)/i', $line, $m)) {
                $parts[] = rtrim(trim($m[2]), ';');
            }
        }
        return implode(';', $parts);
    }

    /**
     * Обновляет запись миграции (sql_str, error, str_rollback) по ID.
     */
    private function updateMigrateRecord(int $id, array $data): void
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $escaped = $this->pdo()->quote($value);
            $sets[] = "`$key` = $escaped";
        }
        if (!empty($sets)) {
            $this->q("UPDATE `migrate` SET " . implode(', ', $sets) . " WHERE `id` = $id");
        }
    }

    /**
     * Выполняет SQL-файлы из указанной директории.
     * Возвращает количество выполненных запросов.
     */
    private function runMigrationsFromDir(string $dir, string $label, int &$cq): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        $files = array_values(array_filter($files, fn($f) => $f !== '.' && $f !== '..'));
        natsort($files);

        foreach ($files as $name) {
            $query = file_get_contents($dir . "/$name");
            if (empty($query)) {
                continue;
            }

            $hash = md5($query);
            if (!empty($this->find(['hash' => "$hash"]))) {
                continue;
            }

            // Парсим Rollback
            $rollbackSql = $this->parseRollback($query);

            // Создаём запись миграции
            $migrateId = $this->create([
                'name' => "[$label] $name",
                'hash' => $hash,
                'str_rollback' => $rollbackSql,
            ]);

            if ($migrateId === false) {
                Console::text("Ошибка вставки записи миграции: $name", Console::RED);
                continue;
            }

            $hasError = false;
            $executedQueries = [];

            foreach (explode(";", $query) as $q) {
                $trimmed = trim($q);
                if (empty($trimmed)) {
                    continue;
                }

                // Пропускаем строки с Rollback
                if (preg_match('/^\s*(#|--|\/\/)\s*Rollback/i', $trimmed)) {
                    continue;
                }

                try {
                    $this->q($trimmed);
                    $executedQueries[] = $trimmed;
                    Console::text(($cq + 1) . ") [$label] Выполнен: " . str_replace("\n", "", iconv_substr($trimmed, 0, 50, 'UTF-8')), Console::GREEN);
                } catch (Error | Exception $e) {
                    $errorMsg = $e->getMessage();
                    Console::text("Error в {$name}: " . $errorMsg, Console::RED);
                    $this->updateMigrateRecord((int)$migrateId, [
                        'sql_str' => $trimmed,
                        'error' => $errorMsg,
                    ]);
                    $hasError = true;
                    break;
                }
                $cq++;
            }

            if (!$hasError) {
                $this->updateMigrateRecord((int)$migrateId, [
                    'sql_str' => implode(";\n", $executedQueries),
                ]);
            }
        }
    }

    /**
     * Запуск миграций (up).
     */
    private function up()
    {
        $this->ensureMigrateTable();

        $cq = 0;

        // Сначала выполняем внутренние миграции фреймворка
        $this->runMigrationsFromDir($this->FRAMEWORK_MIGRATE_DIR, 'framework', $cq);

        // Затем пользовательские миграции
        $this->runMigrationsFromDir($this->DIR, 'app', $cq);

        Console::text("=====================", Console::YELLOW);
        if ($cq == 0) {
            Console::text("Новых миграций нет", Console::YELLOW);
        } else {
            Console::text("Выполнено миграций: $cq", Console::YELLOW);
        }
    }

    /**
     * Откат последней миграции (down).
     * Выполняет Rollback-запросы из поля str_rollback последней записи migrate.
     */
    private function down(): void
    {
        $this->ensureMigrateTable();

        // Ищем последнюю миграцию, у которой есть str_rollback
        $last = $this->q(
            "SELECT * FROM `migrate` WHERE `str_rollback` IS NOT NULL AND `str_rollback` != '' ORDER BY `id` DESC LIMIT 1"
        )->fetch();

        if (empty($last)) {
            Console::text("Нет миграций для отката", Console::YELLOW);
            return;
        }

        $rollbackSql = $last['str_rollback'];
        $name = $last['name'];

        Console::text("Откат миграции: $name", Console::YELLOW);

        $cq = 0;
        foreach (explode(";", $rollbackSql) as $q) {
            $trimmed = trim($q);
            if (empty($trimmed)) {
                continue;
            }

            try {
                $this->q($trimmed);
                Console::text(($cq + 1) . ") Откат: " . str_replace("\n", "", iconv_substr($trimmed, 0, 50, 'UTF-8')), Console::GREEN);
            } catch (Error | Exception $e) {
                Console::text("Error при откате: " . $e->getMessage(), Console::RED);
            }
            $cq++;
        }

        // Удаляем запись о миграции
        $this->q("DELETE FROM `migrate` WHERE `id` = " . (int)$last['id']);

        Console::text("=====================", Console::YELLOW);
        Console::text("Миграция $name откачена", Console::YELLOW);
    }
}
