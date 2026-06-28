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
        Console::text("Web: $host", "green");
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
}
