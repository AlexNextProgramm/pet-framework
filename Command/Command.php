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
            case 'build_sample':
                (new Build())->architecture();
                break;
            case 'load':
                ConnectFtp::load();
                break;
            case 'migrate':
                MigrateCommand::init('migrate');
                break;
            case "make:model":
                (new MakeModel($comm[2] ?? null));
                break;
            case "make:apache":
                (new Apache())->setVirtualHost($comm[2] ?? null);
                break;
            case "make:cert":
                (new Apache())->setCert($comm[2] ?? null);
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

    private function make(string $type, $comm)
    {
        if (count($comm) < 2) die("Требуеться установить имя");
        $name = $comm[2];
        if ($type == "controller") {

            $sample = file_get_contents(__DIR__ . "/sample/controller.sample.php");
            $sample = str_replace('NAME', $name, $sample);
            file_put_contents(self::ROOT . $this->NAME_DIR_PROJECT . Build::APPNAME . "/Controller/$name" . "Controller.php", $sample);
        }
    }
}
