<?php
namespace Pet\Git;

use Error;
use Exception;
use Pet\Command\Console\Console;
use Pet\Migration\MigrateCommand;

class Monitor{
    public $intervalSeconds = 300;
    public $branch = "main";
    public function __construct() {
    }

    static function init(){
        $git = new Monitor();

        while (true) {
            if (!$git->isChange()) {
                sleep($git->intervalSeconds);
                continue;
            }

            $git->composerUpdate();
            $git->startNpm();
            $git->startMigrate();

            sleep($git->intervalSeconds);
        }
    }

    public function isChange():bool 
    {
        exec('git fetch', $outputFetch, $returnFetch);
        if ($returnFetch !== 0) {
            Console::text("Ошибка при выполнении git fetch", Console::RED);
            sleep($this->intervalSeconds);
            return false;
        }

        $cmdDiff = "git rev-list HEAD...origin/$this->branch --count";
        $count = trim(shell_exec($cmdDiff));
        if($count > 0){
            exec('git pull', $output, $result);
            return true;
        }
        return false;
    }


    public function startNpm(){
        $npmBuildCmd = 'npm install && npm run prod';
        $outputNpm = [];
        $returnNpm = 0;
        exec($npmBuildCmd, $outputNpm, $returnNpm);
        if ($returnNpm !== 0) {
            Console::log("Ошибка сборки", Console::RED);
        }

    }
    public function startMigrate(){
        try{
            MigrateCommand::init('migrate');
        }catch(Error|Exception $e){
            Console::log("Ошибки миграций", Console::RED);
            Console::text($e->getMessage());
        }
    }
    public function composerUpdate(){
        $composer = "composer update";
        exec($composer, $output, $return);
        if($return !== 0) {
            Console::log("Ошибка обновления композера", Console::RED);
        }
    }
}