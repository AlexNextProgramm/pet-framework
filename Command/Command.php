<?php

namespace Pet\Command;

use Pet\Git\Monitor;
use Pet\Command\Console\Console;
use Pet\Command\FTP\ConnectFtp;
use Pet\Migration\MigrateCommand;
use Pet\Model\MakeModel;

class Command {

    public string $NAME_DIR_PROJECT;

    public function __construct(array $command)
    {
        $this->NAME_DIR_PROJECT = PUBLIC_DIR;
        $this->stand($command);
    }

    public static function init(array $comm): self
    {
        return new self($comm);
    }

    private function stand(array $comm): void
    {
        // Извлекаем имя команды (индекс 1) и аргумент (индекс 2) до удаления первого элемента
        $commandName = trim($comm[1] ?? '');
        $argument = $comm[2] ?? null;

        switch ($commandName) {
            case 'serve':
                $this->server();
                break;
            case 'socket':
                $this->startSocket($argument);
                break;
            case 'load':
                ConnectFtp::load();
                break;
            case 'load-diff':
                ConnectFtp::loadDiff();
                break;
            case 'migrate':
                MigrateCommand::init('migrate');
                break;
            case "make:model":
                new MakeModel($argument);
                break;
            case "list:model":
                $this->listModels();
                break;
            case "list:controller":
                $this->listControllers();
                break;
            case "info":
                $this->info();
                break;
            case "git-monitor":
                Monitor::init();
                break;
            case "git-update":
                Monitor::initOne();
                break;
            case "env":
                $this->env();
                break;
            default:
                echo "no command ";
        }
    }


    private function server(): void
    {
        $host = URLDEV;
        $hostName = str_replace(['https://', 'http://'], '', $host);
        Console::link($host, $host, 'green');
        exec("php -S $hostName -t \"{$this->NAME_DIR_PROJECT}/\"");
    }

    private function startSocket(?string $name): void
    {
        if ($name === null) {
            Console::text("Не указано имя сокета", Console::RED);
            return;
        }
        $script = SOCKET_DIR . DS . "$name.php";
        if (!file_exists($script)) {
            Console::text("Файл сокета не найден: $script", Console::RED);
            return;
        }
        include $script;
    }

    private function info(): void
    {
        $info  = include __DIR__ . '/info.php';
        Console::text("\n\r HELLO FRAMEWORK PET", Console::YELLOW);
        Console::text("======================================\n\r", Console::YELLOW);
        foreach ($info as $k => $v) {
            Console::text($k . '   -   ' . $v, Console::GREEN);
        }
        Console::text("\n\r======================================\n\r", Console::YELLOW);
    }

    private function env(): void
    {
        $envFile = ROOT . DS . '.env';

        if (file_exists($envFile)) {
            Console::text("Файл .env уже существует: $envFile", Console::GREEN);
            return;
        }

        $template = '# Пет проект
# ============

# Основные директории
PUBLIC_DIR="dist"
ROUTER_DIR="router"
MIGRATE_DIR="database/migrate"
SOCKET_DIR="socket"
URLDEV="http://localhost:8000"

# База данных
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=pet
DB_USER=root
DB_PASSWORD=

# FTP (для команды load)
FTP_HOST=
FTP_LOGIN=
FTP_PASSWORD=
FTP_PORT=22
FTP_HOST_DIR=/
FTP_DIR_EXEPTION=.git|node_modules|vendor
FTP_FILE_EXEPTION=.gitignore|*.log

# Внешние модули (автозагрузка, через ||)
EXTERNAL_MODULE=

# Assets
UPLOADS_URL=/uploads
IMG_RELAT=view/assets/img

# Для сборки (webpack, vite и т.д.)
DIST=dist

# Дополнительные константы (определяются в .env и доступны как PHP-константы)
LOG="../log/admin.log"
JS="view/assets/js/[name][hash].js"
CSS="./view/assets/css/[name][hash].css"
SVG="[PUBLIC_DIR][DS]view/img"
UPLOADS="../../../view/uploads/"

# Для clear (множество через ||)
CLEAR="view/assets/**"
IMG="view/assets/img"
FONT="view/assets/fonts"
TEMPLATE="./head.php"
APP="App"
';

        file_put_contents($envFile, $template);

        if (file_exists($envFile)) {
            Console::text("Файл .env успешно создан: $envFile", Console::GREEN);
            Console::text("Отредактируйте его под свой проект.", Console::YELLOW);
        } else {
            Console::text("Ошибка: не удалось создать файл .env", Console::RED);
        }
    }

    /**
     * Определяет путь к поддиректории приложения (Model, Controller и т.д.)
     *
     * @param string $subDir Имя поддиректории (например, 'Model', 'Controller')
     * @return string|null Полный путь или null, если не найдена
     */
    private function resolveAppDir(string $subDir): ?string
    {
        $possibleDirs = [];

        // Вариант 1: ROOT . DS . PUBLIC_DIR . DS . APP (как в MakeModel)
        $possibleDirs[] = ROOT . DS . PUBLIC_DIR . DS . APP . DS . $subDir;

        // Вариант 2: если PUBLIC_DIR уже абсолютный (содержит [ROOT])
        if (str_starts_with(PUBLIC_DIR, DS)) {
            $possibleDirs[] = PUBLIC_DIR . DS . APP . DS . $subDir;
        }

        // Вариант 3: если APP уже содержит полный путь
        if (str_starts_with(APP, DS)) {
            $possibleDirs[] = APP . DS . $subDir;
        }

        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Вывод списка моделей приложения
     *
     * Сканирует директорию APP/Model/ и выводит таблицу с именами моделей,
     * соответствующими таблицами БД и файлами.
     *
     * @return void
     */
    private function listModels(): void
    {
        $modelDir = $this->resolveAppDir('Model');

        if ($modelDir === null) {
            Console::text("Директория моделей не найдена.", Console::RED);
            Console::text("Проверьте пути: PUBLIC_DIR=" . PUBLIC_DIR . ", APP=" . APP, Console::YELLOW);
            return;
        }

        $files = scandir($modelDir);
        $models = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $modelDir . DS . $file;

            if (!is_file($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);

            // Пытаемся получить таблицу из свойства модели через отражение
            $table = '—';
            $fullClass = APP . '\\Model\\' . $className;

            if (class_exists($fullClass) && is_subclass_of($fullClass, \Pet\Model\Model::class)) {
                try {
                    $ref = new \ReflectionClass($fullClass);
                    if ($ref->hasProperty('table')) {
                        $prop = $ref->getProperty('table');
                        $prop->setAccessible(true);
                        $instance = $ref->newInstanceWithoutConstructor();
                        $tableValue = $prop->getValue($instance);
                        if (!empty($tableValue)) {
                            $table = $tableValue;
                        }
                    }
                } catch (\Throwable $e) {
                    $table = '?';
                }
            }

            $models[] = [
                'Модель' => $className,
                'Таблица' => $table,
                'Файл' => $file,
            ];
        }

        if (empty($models)) {
            Console::text("Модели не найдены в: $modelDir", Console::YELLOW);
            return;
        }

        Console::header('Список моделей', 'cyan', 60);
        Console::newLine();
        Console::table($models, ['Модель', 'Таблица', 'Файл'], 'cyan');
        Console::newLine();
        Console::text("Всего моделей: " . count($models), 'green');
    }

    /**
     * Извлекает имена публичных методов из PHP-файла без его выполнения.
     *
     @param string $filePath Полный путь к файлу
     @return array Список имён методов
     */
    private function extractPublicMethods(string $filePath): array
    {
        $methods = [];
        $content = file_get_contents($filePath);

        if ($content === false) {
            return $methods;
        }

        // Удаляем многострочные комментарии
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        // Удаляем однострочные комментарии
        $content = preg_replace('/\/\/.*/', '', $content);

        // Ищем публичные методы: public [static] function methodName(
        preg_match_all(
            '/public\s+(?:static\s+)?function\s+(\w+)\s*\(/',
            $content,
            $matches
        );

        if (!empty($matches[1])) {
            // Список методов базового контроллера, которые не являются экшенами
            $parentMethods = [
                'json', 'redirect', 'back', 'withInput', 'withErrors',
                'render', 'renderPartial', 'callMiddleware', 'middleware',
                'saveFile', 'saveUploadedFile', 'deleteFile',
                'setData', 'getData', 'isPost', 'isAjax',
                'csrf_token', 'validateCsrf', 'cacheResponse', 'noCache',
            ];

            foreach ($matches[1] as $method) {
                if (!in_array($method, $parentMethods, true)) {
                    $methods[] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * Вывод списка контроллеров приложения
     *
     * Сканирует директорию APP/Controller/ и выводит таблицу с именами контроллеров,
     * списком экшенов (публичных методов) и файлами.
     *
     * @return void
     */
    private function listControllers(): void
    {
        $controllerDir = $this->resolveAppDir('Controller');

        if ($controllerDir === null) {
            Console::text("Директория контроллеров не найдена.", Console::RED);
            Console::text("Проверьте пути: PUBLIC_DIR=" . PUBLIC_DIR . ", APP=" . APP, Console::YELLOW);
            return;
        }

        $files = scandir($controllerDir);
        $controllers = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $controllerDir . DS . $file;

            if (!is_file($path) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);

            // Извлекаем экшены через парсинг файла (без загрузки класса)
            $actions = $this->extractPublicMethods($path);

            $controllers[] = [
                'Контроллер' => $className,
                'Экшены' => !empty($actions) ? implode(', ', $actions) : '—',
                'Файл' => $file,
            ];
        }

        if (empty($controllers)) {
            Console::text("Контроллеры не найдены в: $controllerDir", Console::YELLOW);
            return;
        }

        Console::header('Список контроллеров', 'violet', 60);
        Console::newLine();
        Console::table($controllers, ['Контроллер', 'Экшены', 'Файл'], 'violet');
        Console::newLine();
        Console::text("Всего контроллеров: " . count($controllers), 'green');
    }
}
