<?php
namespace Pet\DataBase;

use PDO;
use Pet\DataBase\DB;

class Select extends DB {
    public $table = '';
    public $column = [];
    public $strQuery = "";
    public $strWhere = "";

    public function __construct() {
        $this->conn();
    }

    /**
     * select
     *
     * @param  mixed $ArrayColumnAndValue
     * @return Select
     */
    public function select(array $ArrayColumnAndValue = []): Select {
        $this->strWhere = "WHERE ";
        $key = array_keys($ArrayColumnAndValue);
        foreach ($key as $column) {
            $this->strWhere .= " {$this->table}.{$column} = {$ArrayColumnAndValue[$column]}";
        }
        $strColumn = implode(' , ', $this->column);
        $this->strQuery = "SELECT $strColumn FROM `{$this->table}` " . $this->strWhere . ";";
        return  $this;
    }

    /**
     * fetch
     *
     * @return array
     */
    public function fetch(): array {
        return $this->q($this->strQuery)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>