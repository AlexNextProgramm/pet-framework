<?php
namespace Pet\Migration;

use Exception;
use PDO;
use Pet\DataBase\DB;
use Pet\Migration\Table;

class Schema extends DB{
    public $QUERY = '';
    public $DB_NAME = DB_NAME;
    static $ERROR = '';

    private function set()
    {
        $result = false;
        try {
            $result =  $this->q($this->QUERY);
        } catch (Exception $e) {
          self::$ERROR = $e->getMessage();
        }

        return $result;
    }

    static function create($name , callable $callable)
    {
        if(Schema::isTable($name)) return false;
        $Schema = new self();
        $table = new Table();

        //  Авто cоздание id cdate;
        $table->id('id');
        $table->timestamp('cdate');

        if($callable) $callable($table);
    
        $telo = implode(' , ', $table->param);
        $Schema->QUERY = "CREATE TABLE `{$Schema->DB_NAME}`.`$name` ($telo) {$table->engine};";
        $Schema->set();
    }

    static function drop($table)
    {
        $Schema = new self();
        $Schema->QUERY = "DROP TABLE `{$Schema->DB_NAME}`.`$table`";
        $Schema->set();
    }

    static function isTable($name): bool
    {
        $Schema = (new self());
        $result = $Schema->q("SHOW TABLES FROM `{$Schema->DB_NAME}` LIKE '$name';")->fetch(PDO::FETCH_ASSOC);
        $result = empty($result) ? [] : $result;
        return in_array($name, $result);
    }


}
?>