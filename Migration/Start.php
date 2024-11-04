<?php

namespace Pet\Migration;

use Error;
use Exception;
use Migrate;
use Pet\Command\Console\Console;
use Pet\Migration\Track;
use Pet\Model\Model;

class Start {
    private $DIR = '';
    public $hash = '';
    public $command = '';
    public $isParam = false;
    public $param = false;
    public $endFile = '';

    function __construct() {
        $this->DIR = ROOT_DIR . env('MIGRATE_DIR');
    }

    static function init($command) {
        $Start = new self();
        $Start->setParam($command);
        if (!is_dir($Start->DIR)) {
            return Console::text("ERROR: Директория миграций не найдена", 'red');
        }
        $scanfile = $Start->scandir();
        Migration::createTableMigrate();
        $track =  new Track();
        $Start->mig($scanfile, $track);
    }

    public function mig($scanfile, Model $track) {
        $static = 0;

        if($this->param == 'end') $scanfile = array_reverse($scanfile); // при  end нужен обратный порядок 
        $isEndBack = false;
        $isUpOne = false;

        foreach ($scanfile as $i => $file) {

            $track->find(['name' => $file]);

            if($this->param == 'one' && $isUpOne) continue;

            if($this->param == 'end' && !$track->isInfo() || $this->param == 'end' && $isEndBack) continue;

            if ($track->isInfo() && $track->v('status') == 1 && $this->command != 'migrate:back') continue;

            if (!$track->isInfo() || ($track->isInfo() && $track->v('status') == 0 ) || ($track->isInfo() && $this->command == 'migrate:back')) {
                $static++;
                $status = 0;

                try {
                    require($this->DIR . "/$file");
                    $name = str_replace('.php', '', explode('_', $file)[2]);
                    $MIG = new $name();
                    if ($this->command == 'migrate:up') $MIG->up();
                    if ($this->command == 'migrate:back') $MIG->back();
                  
                } catch (Error $e) {
                    $track->setUp('name', ['name' => $file, 'status' => $status, 'error' => $e->getMessage()]);
                    $isEndBack = true;
                    Console::text("ERROR: $file - Миграция не может быть достигнута из-за фатальной ошибки!", 'red');
                    continue;
                }
                $status =  Schema::$ERROR == '' ? 1 : 0;
                $query =  Schema::$SchemaQuery;

                if($this->command == 'migrate:up'){
                    $track->setUp('name', ['name' => $file, 'status' => $status, 'error' => Schema::$ERROR]);
                    $isUpOne = true;
                }

                if($this->command == 'migrate:back'){
                    $track->del(['name' => $file]);
                    $isEndBack = true;
                }

                if ($status == 0){
                    Console::text("ERROR: $file - Миграция не может быть достигнута из-за синтаксической ошибки sql запроса!", 'red');
                    Console::text('QUERY: ' .  $query , 'yellow');
                }
                if ($status == 1) Console::text("Успешная миграция  $file", "green");
                Schema::$ERROR = '';
            }
        }

        if ($static == 0)  Console::text("WARNING: Нет новых миграций!", "yellow");
    }

    static function create($name) {

        $Start = new self();
        if (empty($name[2])) return Console::text('ERROR: Нет имени миграции', 'red');

        $name = $name[2];
        $scan = $Start->scandir($number);

        foreach ($scan as $file) {
            $n = explode('_', $file);
            if (!empty($n[2]) && str_replace('.php', '', $n[2]) == $name) {
                return Console::text('ERROR: Такое имя миграции существует, задайте новое', 'red');
            }
        }

        $text = file_get_contents(__DIR__ . '/../Command/sample/mig.sample.php');
        $text = str_replace('{name}', $name, $text);
        $date = date('Ymd');
        file_put_contents($Start->DIR . "/$number" . "_$date" . "_$name.php", $text);
    }

    private function setParam($command)
    {
        $this->command = $command;
        $this->isParam  = !empty(explode(':', $command)[2]);
        $this->param = $this->isParam ? trim(explode(':', $command)[2]) : '';
        $this->command = $this->isParam   ? explode(':', $command)[0].':'.explode(':', $command)[1] : $command;
    }

    public function scandir(&$n = 0) {

        $result = [];
        $scan  = scandir($this->DIR);

        foreach ($scan as $v) {
            if ($v == '.' || $v == '..') continue;
            if (explode('_', $v)[0] >= $n) $n = explode('_', $v)[0];
            $result[] = $v;
        }

        usort($result, function ($file, $file2) {
            $a = explode('_', $file)[0];
            $b =  explode('_', $file2)[0];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $n++;

        return $result;
    }
}
