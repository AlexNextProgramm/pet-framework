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
        parent::__construct();
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
        if (!$this->isTable()) {
            $this->q(
                "CREATE TABLE `migrate` (
                    `id` INT NOT NULL AUTO_INCREMENT ,
                    `name` VARCHAR(500) NULL DEFAULT NULL , 
                    `hash`  VARCHAR(500) NULL DEFAULT NULL , 
                    `cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                    PRIMARY KEY (`id`)  
                    ) ENGINE = InnoDB;"
            );
        }

        $cq = 0;
        dirEach($this->DIR, callFile: function ($name) use (&$cq){
            $query = file_get_contents($this->DIR . "/$name");
            if (empty($query)) {
                Console::text("Пустой файл миграции", 'red');
            }
            $hash = md5($query);
            if (empty($this->find(['hash' => "$hash"]))) {
                $this->insert(['name' => "$name", 'hash' => "$hash"]);
                foreach (explode(";", $query) as $q) {
                    try {
                        if (empty(trim($q))) {
                            continue;
                        }
                        $Query = $this->q($q);
                        if ($Query) {
                            $Query->fetch();
                            Console::text(($cq + 1) . ") Выполнен: " . str_replace("\n", "", iconv_substr(trim($q), 0, 50, 'UTF-8')), Console::GREEN);
                        }
                    } catch (Error | Exception $e) {
                        Console::text("Error: " . $e->getMessage(), Console::RED);
                    }
                    $cq++;
                }
            }
        });
        Console::text("=====================", Console::YELLOW);
        if ($cq == 0) {
            Console::text("Новых миграций нет", Console::YELLOW);
        } else {
            Console::text("Выполнено миграций", Console::YELLOW);
            Console::text("$cq", Console::YELLOW);
        }
    }
}
