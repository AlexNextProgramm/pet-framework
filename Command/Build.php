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
        $sample = file_get_contents(__DIR__."/sample/$namesample.php");
        if(count($rename)> 0){
            foreach($rename as $name=>$value) $sample = str_replace($name, $value, $sample);
        }
        file_put_contents($this->OUT_DIR_VENDOR."/$namefile", $sample );
    }
    
    function build_architecture(){
        $this->build_file('pet.sample', 'pts');

        if(!is_dir('dist/PHP/controller')) mkdir('dist/PHP/Controller', 0777, true);
        if(!is_dir('dist/view')) mkdir('dist/view', 0777, true);
        if(!is_dir('dist/router')) mkdir('dist/router', 0777, true);

        $this->build_file('index.sample', 'dist/index.php');
        $this->build_file('config.constant', 'dist/config.constant.php');
        $this->build_file('home.sample', 'dist/view/home.php');
        $this->build_file('.env.sample', '.env');
        $this->build_file('web.router.sample', 'dist/router/web.php');
        $this->build_file('controller.sample', 'dist/PHP/Controller/HomeController.php', 
        ["NAME"=>"Home"]);
        
    }

    function search_dir_vendor(){
        return str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
    }
}

$const  = new Build();