<?php

namespace Pet\Command;

use Command;

class Build{
    public $OUT_DIR_VENDOR = '';

     public function __construct() {
       $this->OUT_DIR_VENDOR = $this->search_dir_vendor();
       $this->build_architecture();
    }
    
    function build_file($namesample = '', $namefile, $rename = []){
        $sample = file_get_contents(__DIR__."/sample/$namesample");
        if(count($rename)> 0){
            foreach($rename as $name=>$value) $sample = str_replace($name, $value, $sample);
        }
        file_put_contents($this->OUT_DIR_VENDOR."/$namefile", $sample );
    }
    
    function build_architecture(){
        $this->build_file('pet.sample.php', 'pet');

        if(!is_dir('dist/PHP/Controller')) mkdir('dist/PHP/Controller', 0777, true);
        if(!is_dir('dist/view/css')) mkdir('dist/view/css', 0777, true);
        if(!is_dir('dist/view/img')) mkdir('dist/view/img', 0777, true);
        if(!is_dir('dist/router')) mkdir('dist/router', 0777, true);


        $this->build_file('index.sample.php', 'dist/index.php');
        $this->build_file('config.constant.php', 'dist/config.constant.php');
        $this->build_file('home.sample.php', 'dist/view/home.php');
        $this->build_file('.env.sample.php', '.env');
        $this->build_file('web.router.sample.php', 'dist/router/web.php');
        $this->build_file('controller.sample.php', 'dist/PHP/Controller/HomeController.php', 
        ["NAME"=>"Home"]);

        
        $this->build_file("style.sample.css", 'dist/view/css/style.css');
        $this->copy('/img/logo.png','/dist/view/img/logo.png');
    }

    function search_dir_vendor(){
        return str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
    }

    private function copy($file, $fileOut){
       copy( __DIR__."/sample/$file", $this->OUT_DIR_VENDOR."/$fileOut",); 
    }
}

$const  = new Build();