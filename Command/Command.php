<?php

include_once(__DIR__.'/../function.php');
class Command{
   const ROOT_DIR = ROOT_DIR;

    public function __construct($command){
       
        $this->routCommand($command);
    }

    static function init($comm){
       
        return new Command($comm);
    }

    private function routCommand($comm){
        unset($comm[0]);
        switch (trim($comm[1])) {
            case 'serve':$this->server();
                break;
        default:
                echo "no command ";
        }
    }


    private function server(){

        $host = env("URLDEV");
        $hostName = str_replace(['https://', 'http://'], '', $host);
        exec("php -S $hostName -t dist/");
        echo "\033[02;32m  \n\r \n\r   site: $host \033[0m \n\r \n\r";
    }
}
?>