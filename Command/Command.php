<?php

namespace Pet\Command;


use Pet\Apache\Apache;
use Pet\Command\Console\Console;
use Pet\Command\FTP\ConnectFtp;
use Pet\Migration\MigrateCommand;
use Pet\Model\MakeModel;

class Command {
    const ROOT = ROOT . DIRECTORY_SEPARATOR;
    public $NAME_DIR_PROJECT;

    public function __construct($command)
    {
        $this->NAME_DIR_PROJECT = PUBLIC_DIR;
        $this->stand($command);
    }

    static function init($comm) {
        return new Command($comm);
    }

    private function stand($comm)
    {
        unset($comm[0]);
        $inCommands = trim($comm[1]);

        switch (trim($comm[1])) {
            case 'serve':
                $this->server();
                break;
            case 'socket':
                $this->startSocket($comm[2]);
                break;
            case 'load':
                ConnectFtp::load();
                break;
            case 'migrate':
                MigrateCommand::init('migrate');
                break;
            case "make:model":
                (new MakeModel($comm[2] ?? null));
            case "info":
                $this->info(); 
                break;
            default:
                echo "no command ";
        }
    }


    private function server()
    {
        $host = URLDEV;
        $hostName = str_replace(['https://', 'http://'], '', $host);
        Console::text("Web: $host", "green");
        exec("php -S $hostName -t \"{$this->NAME_DIR_PROJECT}/\"");
    }
    private function startSocket($name)
    {
        $script = SOCKET_DIR .DS. "$name.php";
        include $script;
    }

    private function info(){
        $info  = include __DIR__.'/info.php';
        Console::text("\n\r HELLO FRAMEWORK PET", Console::YELLOW);
        Console::text("======================================\n\r", Console::YELLOW);
        foreach($info as $k => $v){
            Console::text($k . '   -   ' . $v, Console::GREEN);
        }
        Console::text("\n\r======================================\n\r", Console::YELLOW);
    }
}
