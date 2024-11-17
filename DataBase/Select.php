<?php

namespace Pet\DataBase;

use PDO;
use Pet\DataBase\DB;
use Pet\Model\Model;
use Pet\Tools\Tools;

trait Select{

    public $strSelect = '';

    /**
     * select
     *
     * @param  mixed $ArrayColumnAndValue
     * @return Select
     */
    public function select(array $ArrayColumnAndValue = [], $AS = []): Model {
        $strColumn =  Tools::is_assos($ArrayColumnAndValue) == 'assos' ?
            array_keys($ArrayColumnAndValue) : $ArrayColumnAndValue;
        if (count($this->column) != 0) {
            if (count(array_diff($ArrayColumnAndValue, $this->column)) != 0) {
                $strColumn = $this->column;
            }
        }
         $strSelect = '*';
        if(count($strColumn) == 0){
            $strSelect = "{$this->table}.*";
        } else {
            $strSelect .=  Tools::array_implode(",", $strColumn, "`{$this->table}`.`[val]`");
        }
        if(count($AS) > 0){
            $strSelect .= ", ". Tools::array_implode(",", $AS, "[key] AS [val]");
        }
        $table = $this->tableChanged ?? $this->table;

        $this->strQuery = "SELECT {$strSelect} FROM `$table` ";
        return  $this;
    }


    /**
     * or
     *
     * @param  mixed $assos
     * @param  mixed $tie
     * @return Select
     */
    public function or($assos, $tie = ''): Model {
        if (!str_contains($this->strWhere, 'WHERE')) $this->strWhere = " WHERE ";
        $this->strWhere .= "(" . Tools::array_implode(' OR ', $assos, "`[key]`='[val]'") . ") $tie ";
        return $this;
    }

    public function orn($name, $array , $tie = ''){
        if (!str_contains($this->strWhere, 'WHERE')) $this->strWhere = " WHERE ";
        foreach($array as $k => $v) if(count($array) != $k + 1 ) $array[$k] = $v." OR ";
        $this->strWhere .= "($name =".implode("$name =", $array).")" . $tie;
        return $this;
    }
    /**
     * and
     *
     * @param  mixed $assos
     * @param  mixed $tie
     * @return Select
     */
    public function and($assos,  $tie = ''): Model {
        if (!str_contains($this->strWhere, 'WHERE')) $this->strWhere = " WHERE ";
        if (count($assos) != 0) {
            $str = Tools::array_implode(' AND ', $assos, "`{$this->table}`.`[key]` = '[val]'");
            $str = count($assos) > 1 ? "( $str ) " : $str;
            $this->strWhere .= " $str $tie ";
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
    public function join(string $table, array $select): Model {
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
    public function leftJoin(string $table, array $select): Model {
        $this->strJoin .= "LEFT JOIN $table ON " . Tools::array_implode(' AND ', $select, "`{$this->table}`.`[key]` = `{$table}`.`[val]`");
        return $this;
    }


    /**
     * where
     *
     * @param  mixed $str
     * @return Select
     */
    public function where($str = ''): Model {
      
        $this->strWhere = "WHERE $str";
        return $this;
    }

    public function limit($limit = 100, $DESC = "DESC", $cl = 'id'){
        $this->strWhere .= "ORDER BY $cl $DESC LIMIT $limit;";
        return $this;
    }

    public function max($column = 'id'){
       return  $this->q("SELECT MAX(`$column`) FROM `{$this->table}`")->fetch()['MAX(`id`)'];
    }
}
