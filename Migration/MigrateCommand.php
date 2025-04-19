<?php

namespace Pet\Migration;

use Error;
use Exception;
use Pet\Command\Console\Console;
use Pet\Model\Model;

class MigrateCommand extends Model
{

    private $DIR = '';
    public $hash = '';
    public $command = '';
    public $isParam = false;
    public $param = false;
    public $endFile = '';

    public $table = 'migrate';

    public function __construct() {
        $this->DIR = ROOT_DIR . DS . env('MIGRATE_DIR');
        if (!is_dir($this->DIR)) {
            mkdir($this->DIR, 0777);
        }
    }

    public static function init($command)
    {
        $migrate = new self();
        switch ($command) {
            case 'migrate':
                $migrate->up();
                break;
        }
    }

    private function up()
    {
        // проверить существование таблцы
        if ($this->isTable()) {
            $this->q(
                "CREATE TABLE `migrate_local` (
                    `id` INT NOT NULL AUTO_INCREMENT ,
                    `name` VARCHAR(500) NULL DEFAULT NULL , 
                    `hash`  VARCHAR(500) NULL DEFAULT NULL , 
                    `cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                    PRIMARY KEY (`id`)  
                    ) ENGINE = InnoDB;"
            );
        }

        dirEach($this->DIR, callFile: function ($name) {
            $query = file_get_contents($this->DIR . "/$name");
            if (!empty($query)) {
                Console::text("Пустой файл миграции", 'red');
            }
            $hash = md5($query);
            $q = "SELECT * FROM `migrate` WHERE `hash` = '$hash';";
            if (empty($this->q($q))) {
                $this->insert(['name' => "$name", 'hash' => "$hash"]);
                $cq = 1;
                foreach (explode(";", $query) as $q) {
                    try {
                        if (empty(trim($q))) {
                            continue;
                        }
                        if ($this->q($q . ";")) {
                            Console::text($cq . "Выполнен: " . str_replace("\n", "", iconv_substr(trim($q), 0, 50, 'UTF-8')), Console::GREEN);
                        }
                    } catch (Error | Exception $e) {
                        Console::text("Error: " . $e->getMessage(), Console::RED);
                    }
                    $cq++;
                }
            }
        });
    }
}
