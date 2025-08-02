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

        $this->strQuery = "SELECT {$strSelect}" .$this->fromTable();
        $this->SUB = "SELECT";
        return  $this;
    }

    /**
     * show
     *
     * @param  string $value
     * @return Model
     */
    public function show(string $value): Model
    {
        $this->strQuery = "SHOW {$value}" . $this->fromTable();
        $this->SUB = "SHOW";
        return $this;
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
        $table = implode(" as ", explode(" ", $table));
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
                    $sign = $ons[2] ?? "=";
                    $this->strJoin .= $ons[0]. " $sign ". $ons[1];
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
        return $this->whereAdd($str);
    }

    /**
     * whereAdd
     *
     * @param  string $str
     * @param  string $sign
     * @return Model
     */
    public function whereAdd(string $str = '', $sign = "AND"): Model
    {
        if (empty($str)) return $this;
        $where = $this->getWhere();
        $this->strWhere = empty($where) ? " WHERE $str" :  " $where $sign $str" ;
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
    public function orderBy(string $str = "", $toSort = "ASC"): Model
    {
        $this->SUB = " ORDER BY";
        if ($str == "") {
            $this->strOrders = "";
            return $this;
        }

        if ($this->strOrders != "") {
            $this->strOrders .= ", $str $toSort";
        } else {
            $this->strOrders = " ORDER BY $str";
        }

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
        $this->SUB = "GROUP BY";
        if ($str == "") {
            $this->strGroups = "";
            return $this;
        }

        if ($this->strGroups != "") {
            $this->strGroups .= ", $str";
        } else {
            $this->strGroups = " GROUP BY $str";
        }

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
        $table = $this->getTableName();
        $this->strWhere = " WHERE `$table`.`id` = '$id'";
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
