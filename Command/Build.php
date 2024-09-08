<?php

namespace Pet\Command;

class Build{
    public $OUT_DIR_VENDOR = '';

     public function __construct() {
       $this->OUT_DIR_VENDOR = $this->search_dir_vendor();
       $this->build_architecture();
    }
    
    function build_file($namesample = '', $namefile){
        $sample = file_get_contents(__DIR__."/sample/$namesample.php");
        file_put_contents($this->OUT_DIR_VENDOR."/$namefile", $sample );
    }
    
    function build_architecture(){
        $this->build_file('pet.sample', 'pts');
        
        if(!is_dir('dist/PHP/controller')) mkdir('dist/PHP/controller', 0777, true);
        if(!is_dir('dist/view')) mkdir('dist/view', 0777, true);

        $this->build_file('index.sample', 'dist/index.php',);
        $this->build_file('config.constant', 'dist/config.constant.php',);
        $this->build_file('home.sample', 'dist/view/home.php',);
    }

    function search_dir_vendor(){
        return str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
    }
}

$const  = new Build();