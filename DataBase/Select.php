<?php

namespace Pet\DataBase;

use PDO;
use Pet\DataBase\DB;
use Pet\Tools\Tools;

class Select extends DB {
    public $table = '';
    public $column = [];
    public $strQuery = "";
    public $strWhere = "";
    public $isSoftRemoval = false;
    public $strJoin = '';
    public $strSelect = '';
    public function __construct() {
        $this->conn();
    }

    /**
     * select
     *
     * @param  mixed $ArrayColumnAndValue
     * @return Select
     */
    public function select(array $ArrayColumnAndValue = [], $AS = []): Select {
        $strColumn =  Tools::is_assos($ArrayColumnAndValue) == 'assos' ?
            array_keys($ArrayColumnAndValue) : $ArrayColumnAndValue;
        if (count($this->column) != 0) {
            if (count(array_diff($ArrayColumnAndValue, $this->column)) != 0) {
                $strColumn = $this->column;
            }
        }
        if(count($strColumn) == 0)   $this->strSelect = "{$this->table}.*";
        $this->strSelect .=  Tools::array_implode(",", $strColumn, "`{$this->table}`.`[val]`");
        if(count($AS)){
            $this->strSelect .= ", ". Tools::array_implode(",", $AS, "[key] AS [val]");
        }

        $this->strQuery = "SELECT {$this->strSelect} FROM `{$this->table}` ";
        return  $this;
    }

    /**
     * fetch
     *
     * @return array
     */
    public function fetch(): array {
        $this->strQuery = $this->strQuery . $this->strJoin . $this->strWhere;
        $this->whereSyntax($this->strQuery);
        return $this->q($this->strQuery)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * or
     *
     * @param  mixed $assos
     * @param  mixed $tie
     * @return Select
     */
    public function or($assos, $tie = ''): Select {
        if (!str_contains($this->strWhere, 'WHERE')) $this->strWhere = " WHERE ";
        $this->strWhere .= "(" . Tools::array_implode(' OR ', $assos, "`[key]`='[val]'") . ") $tie ";
        return $this;
    }
    /**
     * and
     *
     * @param  mixed $assos
     * @param  mixed $tie
     * @return Select
     */
    public function and($assos,  $tie = ''): Select {
        if (!str_contains($this->strWhere, 'WHERE')) $this->strWhere = " WHERE ";
        if (count($assos) != 0) {
            $this->strWhere .= "(" . Tools::array_implode(' AND ', $assos, "`{$this->table}`.`[key]`='[val]'") . ") $tie ";
        } else {
            $this->strWhere .= " $tie ";
        }
        return $this;
    }

    /**
     * join
     *
     * @param  mixed $table
     * @param  mixed $select
     * @return Select
     */
    public function join(string $table, array $select): Select {
        $this->strJoin .= "JOIN $table ON " . Tools::array_implode(' AND ', $select, "`{$this->table}`.`[key]` = `{$table}`.`[val]`");
        return $this;
    }
     /**
     * join
     *
     * @param  mixed $table
     * @param  mixed $select
     * @return Select
     */
    public function leftJoin(string $table, array $select): Select {
        $this->strJoin .= "LEFT JOIN $table ON " . Tools::array_implode(' AND ', $select, "`{$this->table}`.`[key]` = `{$table}`.`[val]`");
        return $this;
    }


    /**
     * where
     *
     * @param  mixed $str
     * @return Select
     */
    public function where($str = ''): Select {
      
        $this->strWhere = "WHERE $str";
        return $this;
    }

    private function whereSyntax(&$query) {
        $str =  explode('WHERE', $query);
        if (count($str) == 2 && trim($str[1]) == '') {
            $query = $str[0] . 'WHERE 1';
        }
    }
}
