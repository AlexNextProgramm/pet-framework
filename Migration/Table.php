<?php

namespace Pet\Migration;

use Exception;
use Pet\Migration\Common\Common;
use Pet\Migration\Common\Attribute;

class Table {

    use Common;
    public $param = [];
    public $AddIndex = [];
    public $engine = "ENGINE = InnoDB";
    public $tInfo = [];
    public $isChange = false;

    public function string(string $name, int $length = 255): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = "`$name`  $newName  VARCHAR($length) {charset} NOT NULL";
        return new Attribute($this);
    }

    public function id($id = 'id') {
        $this->param[] = "`$id` INT NOT NULL AUTO_INCREMENT";
        $this->param[] = "PRIMARY KEY (`$id`)";
        return new Attribute($this);
    }

    public function text(string $name): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = "`$name` $newName TEXT {charset} NOT NULL";
        return new Attribute($this);
    }

    public function date(string $name): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = "`$name` $newName DATE NOT NULL";
        return new Attribute($this);
    }

    public function datetime(string $name): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = "`$name` $newName DATETIME NOT NULL";
        return new Attribute($this);
    }

    public function int(string $name, int $length = 60): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = " `$name` $newName INT($length) NULL";
        return new Attribute($this);
    }

    public function double(string $name, int $length = 10): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = " `$name` $newName DOUBLE($length) NULL";
        return new Attribute($this);
    }

    public function boolean(string $name): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = " `$name` $newName BOOLEAN NULL";
        return new Attribute($this);
    }

    public function timestamp(string $name = "date_time"): Attribute {
        $newName = $this->changeName($name, $this->isChange);
        $this->param[] = " `$name` $newName DATETIME NULL DEFAULT CURRENT_TIMESTAMP";
        return new Attribute($this);
    }

    public function storage(string $type = "InnoDB") {
        $this->engine = "ENGINE = $type";
    }

    public function index($name, $indexName = '',  $length = '') {
        if ($length != '') $length = "($length)";
        if ($indexName != '') $indexName = "`$indexName`";
        $this->AddIndex[] = "INDEX $indexName (`$name` $length)";
    }

    public function primaryKey($name) {
        $this->AddIndex[] = "PRIMARY KEY (`$name`) USING BTREE";
    }
}
