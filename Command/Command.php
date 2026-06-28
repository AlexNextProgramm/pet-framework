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
}
