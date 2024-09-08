<?php

include_once(__DIR__ . '/../function.php');
class Command
{
    const ROOT_DIR = ROOT_DIR;
    const NAME_DIR_PROJECT = 'dist';

    public function __construct($command)
    {

        $this->routCommand($command);
    }

    static function init($comm)
    {

        return new Command($comm);
    }

    private function routCommand($comm)
    {
        unset($comm[0]);
        switch (trim($comm[1])) {
            case 'serve':
                $this->server();
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
        $folder = self::NAME_DIR_PROJECT;

        echo "\033[02;32m  \n \nsite: $host \033[0m \n \n";
        exec("php -S $hostName -t $folder/");
    }

    private function make(string $type, $comm)
    {
        if (count($comm) < 2) die("Требуеться установить имя");
        $name = $comm[2];
        if ($type == "controller") {

            $sample = file_get_contents(__DIR__ . "/sample/controller.sample.php");
            $sample = str_replace('NAME', $name, $sample);
            file_put_contents(self::ROOT_DIR . self::NAME_DIR_PROJECT . "/PHP/Controller/$name" . "Controller.php", $sample);
        }
    }
}
