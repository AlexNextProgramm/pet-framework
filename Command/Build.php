<?php

namespace Pet\Command;

use Command;
use Pet\Command\Console\Console;

class Build {
     public $isAllReplace = false;
     public $isPetWarning = false;
    function __construct() {
       if(!defined('ROOT')) define('ROOT', $this->search_dir_vendor());
       if (!file_exists(ROOT . '/.env')) {
           $this->copy('.env.sample.php',  ROOT . DIRECTORY_SEPARATOR .'.env');
        }
        include ROOT . '/vendor/autoload.php';
        if(!defined('APP')) define('APP','APP');
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

        $dirCreate = mb_substr($dir, 0, -1);
        if (!is_dir($dirCreate)) mkdir($dirCreate, 0777, true);
        $control = true;

        $isFile = file_exists($dir . $NAME . $names);

        if(!$this->isPetWarning && $isFile){
            Console::text("WARNING: Pet обнаружил что проект уже строился!\nВы точно хотите построить проект по шаблону тогда вы можете потерять некоторые файлы (y/n)?", "yellow");
            Console::input($str);
            if(Console::isYes($str))$this->isAllReplace = true;
            $this->isPetWarning = true;
        }

        if(!$this->isAllReplace && $isFile)
        {
            if($sample != file_get_contents($dir . $NAME . $names)){
                Console::text(" Вы уверены что хотите перезаписать файл $NAME$names? (y/n)");
                Console::input($str);
                if(!Console::isYes($str)) $control = false;
            }
        }
        if($control) file_put_contents( $dir . $NAME . $names, $sample);
    }

    function architecture() {
        define('DS', DIRECTORY_SEPARATOR);
        setConstantEnv(ROOT);
        $public = PUBLIC_DIR;
        $this->setfile('pet');

        $this->setFile('index.php',  $public . DS);
        $this->setFile(
            'Controller.php',
            $public  . DS . APP.'/Controller/',
            [
                "NAME" => "Home",
                "SPACE" => APP.'\\Controller',
            ]
        );
        $this->setFile('home.php', $public . DS . "view/page/");
        $this->setFile('documentation.php', $public . "/view/page/");

        $this->setFile('header.php', $public . "/view/");
        $this->setFile('head.php', $public . "/view/");
        $this->setFile('footer.php', $public . "/view/");

        $this->setFile('style.css', $public . "/view/css/");
        $this->setFile('web.php', $public . "/router/");

        // КОПИ ФАЙЛ
        if(!is_dir($public."/view/img")) mkdir($public."/view/img", 0777, true);
        $this->copy('img/logo.png', $public.'/view/img/logo.png');
        $this->copy('config.constant.php', ROOT. DS .'config.constant.php');
    }

    function search_dir_vendor() {
        return str_replace('\\',DIRECTORY_SEPARATOR, getcwd());
    }

    private function copy($file, $fileOut, ) {
        copy(realpath(__DIR__ . "/sample/$file"),  "$fileOut");
    }
}
