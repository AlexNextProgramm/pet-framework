<?php

namespace Pet\Migration;

use Exception;
use PDO;
use Pet\DataBase\DB;
use Pet\Migration\Table;

class Schema extends DB {
    public $QUERY = '';
    public $DB_NAME = DB_NAME;
    static $ERROR = '';
    private $replace = ['{charset}'];
    private function set() {
        $result = false;
        try {
            $result =  $this->q($this->QUERY);
        } catch (Exception $e) {
            self::$ERROR = $e->getMessage();
        }

        return $result;
    }

    static function create($name, callable $callable) {
        // if(Schema::isTable($name)) return false;
        $Schema = new self();
        $table = new Table();

        //  Авто cоздание id cdate;
        $table->id('id');
        $table->timestamp('cdate');

        if ($callable) $callable($table);

        $telo = implode(' , ', $table->param);
        $telo = str_replace($Schema->replace, '', $telo);
        $Schema->QUERY = "CREATE TABLE `{$Schema->DB_NAME}`.`$name` ($telo) {$table->engine};";

        if (!empty($table->index)) {
            foreach ($table->AddIndex as $i => $add) {
                $table->AddIndex[$i] =  'ALTER TABLE `' . $name . '` ADD ' . $add;
            }
            $Schema->QUERY .= implode(';', $table->AddIndex) . ';';
        }
        // var_dump($Schema->QUERY);
        $Schema->set();
    }

    static function dropTable($table) {
        $Schema = new self();
        $Schema->QUERY = "DROP TABLE `{$Schema->DB_NAME}`.`$table`";
        $Schema->set();
    }
    static function drop($table, $column) {
        $Schema = new self();
        $Schema->QUERY = "ALTER TABLE `$table` DROP `$column`;";
        $Schema->set();
    }

    static function change(string $name, callable $callable) {
        if (!Schema::isTable($name)) return false;
        $Schema = new self();
        $table = new Table();
        $table->isChange = true;
        if ($callable) $callable($table);
        foreach ($table->param as $i => $param) {
            $table->param[$i] = 'ALTER TABLE `' . $name . '` CHANGE ' . $param;
        }
        $Schema->QUERY = str_replace($Schema->replace, '', implode('; ', $table->param)) . ';';
        $Schema->set();
    }

    static function add(string $name, callable $callable) {
        if (!Schema::isTable($name)) return false;
        $Schema = new self();
        $table = new Table();
        if ($callable) $callable($table);
        foreach ($table->param as $i => $param) {
            $table->param[$i] = 'ALTER TABLE `' . $name . '` ADD ' . $param;
        }
        $Schema->QUERY = str_replace($Schema->replace, '', implode('; ', $table->param));
        if (!empty($table->AddIndex)) {
            foreach ($table->AddIndex as $i => $add) {
                $table->AddIndex[$i] =  'ALTER TABLE `' . $name . '` ADD ' . $add;
            }
            $Schema->QUERY .= implode(';', $table->AddIndex);
        }
        $Schema->QUERY .= ';';
        $Schema->set();
        var_dump(Schema::$ERROR);
    }

    static function isTable($name): bool {
        $Schema = (new self());
        $result = $Schema->q("SHOW TABLES FROM `{$Schema->DB_NAME}` LIKE '$name';")->fetch(PDO::FETCH_ASSOC);
        $result = empty($result) ? [] : $result;
        return in_array($name, $result);
    }

    static function sql($query) {
        $Schema = (new self());
        $Schema->QUERY = $query;
        $Schema->set();
    }
}
