<?php

namespace Pet\Command;

class Build{
    public $OUT_DIR_VENDOR = '';

     public function __construct() {
        
       $this->OUT_DIR_VENDOR = $this->search_dir_vendor();
       $this->build_file('pet.sample');

    }
    
    function build_file($namesample = ''){

      $sample = file_get_contents("./sample/$namesample.php");
      file_put_contents($this->OUT_DIR_VENDOR.'pts', $sample );

    }


    function search_dir_vendor(){

        $path = str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
        if(str_contains($path, 'vendor/')){
            return explode('vendor/', $path)[0];
        }else{
            return './../../../';
        }
    }
}

$const  = new Build();