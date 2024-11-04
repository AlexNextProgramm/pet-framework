<?php

namespace Pet\Command;

use Command;
use Pet\Command\Console\Console;

class Build {
     public $isAllReplace = false;
     public $isPetWarning = false;
    function __construct() {
       if(empty(ROOT_DIR)) define("ROOT_DIR", $this->search_dir_vendor() . "/");

        if (!file_exists(ROOT_DIR . '.env')) {
            $this->copy('.env.sample.php', '.env');
        }
        include_once ROOT_DIR . 'vendor/autoload.php';
        $this->architecture();
    }

    function setFile($names = '', $dir = "", $rename = []) {
        $name  = explode('.', $names)[0];
        $ext = empty(explode('.', $names)[1]) ? "php" : explode('.', $names)[1];
        $sample = file_get_contents(__DIR__ . "/sample/$name.sample.$ext");
        $NAME = "";

        if (!empty($rename)) {
            $sample = str_replace(array_keys($rename), array_values($rename), $sample);
            if (!empty($rename['NAME'])) {
                $NAME = $rename['NAME'];
            }
        }

        $dirCreate = mb_substr(ROOT_DIR . $dir, 0, -1);
        if (!is_dir($dirCreate)) mkdir($dirCreate, 0777, true);
        $control = true;

        $isFile = file_exists(ROOT_DIR . $dir . $NAME . $names);

        if(!$this->isPetWarning && $isFile){
            Console::text("WARNING: Pet обнаружил что проект уже строился!\nВы точно хотите построить проект по шаблону тогда вы можете потерять некоторые файлы (y/n)?", "yellow");
            Console::input($str);
            if(Console::isYes($str))$this->isAllReplace = true;
            $this->isPetWarning = true;
        }
        if(!$this->isAllReplace && $isFile)
        {
            if($sample != file_get_contents(ROOT_DIR . $dir . $NAME . $names)){
                Console::text(" Вы уверены что хотите перезаписать файл $NAME$names? (y/n)");
                Console::input($str);
                if(!Console::isYes($str)) $control = false;
            }
        }
        if($control) file_put_contents(ROOT_DIR . $dir . $NAME . $names, $sample);
    }

    function architecture() {
        $public =  env('PUBLIC_DIR', 'dist') . "/";
        $this->setfile('pet');
        $this->setFile('index.php',  $public);
        $this->setFile(
            'Controller.php',
            $public  . 'PHP/Controller/',
            [
                "NAME" => "Home",
                "SPACE" => "PHP\\Controller",
            ]
        );
        $this->setFile('home.php', $public . "/view/");
        $this->setFile('style.css', $public . "/view/css/");
        $this->setFile('web.php', $public . "/router/");

        // КОПИ ФАЙЛ
        if(!is_dir(ROOT_DIR.$public."/view/img")) mkdir(ROOT_DIR.$public."/view/img", 0777, true);
        $this->copy('/img/logo.png', $public.'/view/img/logo.png');
    }

    function search_dir_vendor() {
        return str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
    }

    private function copy($file, $fileOut) {
        copy(__DIR__ . "/sample/$file", ROOT_DIR . "$fileOut",);
    }
}
