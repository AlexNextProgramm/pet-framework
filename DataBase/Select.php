<?php

namespace Pet\DataBase;

use PDO;
use Pet\DataBase\DB;
use Pet\Model\Model;
use Pet\Tools\Tools;

trait Select
{

    public $strSelect = '';
    private $join = "";
    private $or = "";
    public $tableAlias = null;
    /**
     * select
     *
     * @param  $str
     * @return Select
     */
    public function select(string|null ...$column): Model
    {
        $strSelect = "{$this->table}.* ";
        if (!empty($column)) {
            $column = Tools::filter($column, function ($k, $v) {
                if (preg_match("/^[\S]*[ ]{1}[\S]*$/", $v)) {
                    $v = str_replace(' ', ' as ', $v);
                }
                return $v;
            });
            $strSelect = implode(',', $column);
        }

        $from = "FROM `{$this->table}` ".($this->tableAlias? " AS {$this->tableAlias}": "");
        $this->strQuery = "SELECT {$strSelect} $from";
        $this->SUB = "SELECT";
        return  $this;
    }

    /**
     * join
     *
     * @param  mixed $table
     * @param  mixed $select
     * @return Select
     */
    public function join(string $table, string $type = "LEFT" ) : Model
    {
        $this->join = "$type JOIN $table ON ";
        $this->SUB = "JOIN";
        return $this;
    }

    /**
     * on
     * Пример 1  "table1.id = table2.table_id"
     * Пример 2  ["table1.id", "table2.table_id", "="]
     * пример 3  "table1.id = table2.table_id", "AND",  ["table1.id", "table2.table_id", "="]
     * @param  mixed $select
     * @return void
     */
    public function on(string|array ...$select)
    {
        if ($this->join != '' ) {
            foreach ($select as $ons) {
                $this->strJoin  .= "{$this->join}(";
                if (is_array($ons)) {
                    $this->strJoin .= $ons[0]. $ons[2] ?? "=". $ons[1];
                }
                if (gettype($ons) == 'string') {
                    $this->strJoin .=  $ons ;
                }
                $this->strJoin  .= ")";
            }
            $this->join = '';
        }
        $this->SUB = "ON";
        return $this;
    }


    /**
     * where
     *
     * @param string $str
     * @return Model
     */
    public function where(string $str = ''): Model
    {
        $this->strWhere = " WHERE $str";
        $this->SUB = "WHERE";
        return $this;
    }
    public function whereAdd(string $str = '', $sign = "AND"): Model
    {
        $where = $this->getWhere();
        if (empty($where)) {
            $this->strWhere = " WHERE $str";
        } else {
            $this->strWhere =  " WHERE $where $sign $str";
        }
        $this->SUB = "WHERE";
        return $this;
    }

    /**
     * and
     *
     * @param string $str
     * @return Model
     */
    public function and(string $str): Model
    {
        $this->conditions($str, " AND ");
        return $this;
    }

    /**
     * or
     *
     * @param string $str
     * @return Model
     */
    public function or(string $str): Model
    {
        $this->conditions($str, " OR ");
        return $this;
    }

    /**
     * orderBy
     *
     * @param string $str
     * @return Model
     */
    public function orderBy(string $str = ""): Model
    {
        $this->strOrders = " ORDER BY $str";
        $this->SUB = " ORDER BY";
        return $this;
    }

    /**
     * groupBy
     *
     * @param string $str
     * @return Model
     */
    public function groupBy(string $str = ""): Model
    {
        $this->strGroups = " GROUP BY $str";
        $this->SUB = "GROUP BY";
        return $this;
    }

    /**
     * whereId
     *
     * @param string|int $id
     * @return Model
     */
    public function whereId(string|int $id): Model
    {
        $this->strWhere = " WHERE `{$this->table}`.`id` = '$id'";
        $this->SUB = "WHERE";
        return $this;
    }

    /**
     * limit
     *
     * @param  mixed $str
     * @return Model
     */
    public function limit(string|int $str = ""): Model
    {
        $this->strLimit .= " LIMIT $str";
        $this->SUB = "LIMIT";
        return $this;
    }

    public function offset(string|int $str = ""): Model
    {
        $this->strOffset = " OFFSET $str";
        $this->SUB = "OFFSET";
        return $this;
    }

    public function max(string $field = 'id')
    {
        return $this->select("MAX(`$field`) max")->fetch(false)['max'];
    }

    private function conditions($str, $conds): void
    {
        if ($this->SUB = "WHERE") {
            $this->strWhere .= " $conds $str";
        }
    }

    public function getJoin():string
    {
        return $this->strJoin;
    }

    public function getWhere():string
    {
        return $this->strWhere;
    }

}
