<?php

namespace Pet\Command;

use Pet\Command\Console\Console;
use Pet\Command\FTP\ConnectFtp;
use Pet\Migration\Start;

class Command
{
    const ROOT_DIR = ROOT_DIR;
    public $NAME_DIR_PROJECT;

    public function __construct($command)
    {
        $this->NAME_DIR_PROJECT = env("PUBLIC_DIR", 'dist');
        $this->stand($command);
    }

    static function init($comm)
    {
        return new Command($comm);
    }

    private function stand($comm)
    {
        unset($comm[0]);
        switch (trim($comm[1])) {
            case 'serve':
                $this->server();
                break;
            case 'build_sample':
                $this->build();
                break;
            case 'load':
                ConnectFtp::load();
                break;
            case 'migrate':
                    Start::init('migrate');
                    break;
            case 'make:migrate':
                    Start::create($comm);
                    break;
            case "make:controller":
                $this->make(explode(':', $comm[1])[1], $comm);
                break;
            default:
                echo "no command ";
        }
    }


    private function server()
    {
        $host = env("URLDEV");
        $hostName = str_replace(['https://', 'http://'], '', $host);
        Console::text("Web: $host", "green");
        exec("php -S $hostName -t {$this->NAME_DIR_PROJECT}/");
    }

    private function make(string $type, $comm)
    {
        if (count($comm) < 2) die("Требуеться установить имя");
        $name = $comm[2];
        if ($type == "controller") {

            $sample = file_get_contents(__DIR__ . "/sample/controller.sample.php");
            $sample = str_replace('NAME', $name, $sample);
            file_put_contents(self::ROOT_DIR . $this->NAME_DIR_PROJECT . "/PHP/Controller/$name" . "Controller.php", $sample);
        }
    }

    private function build(){
        exec("php ./vendor/pet/framework/Command/Build.php");
    }
}
