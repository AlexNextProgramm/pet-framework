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
     * @param  string|null ...$column
     * @return Model
     */
    public function select(string|null ...$column): Model
    {
        if (empty($column) || (count($column) === 1 && $column[0] === null)) {
            $strSelect = "{$this->table}.* ";
        } else {
            $column = Tools::filter($column, function ($k, $v) {
                if (preg_match("/^[\S]*[ ]{1}[\S]*$/", $v)) {
                    $v = str_replace(' ', ' as ', $v);
                }
                return $v;
            });
            $strSelect = implode(',', $column);
        }

        $this->strQuery = "SELECT {$strSelect}" . $this->fromTable();
        $this->SUB = "SELECT";
        return $this;
    }

    /**
     * Выборка с DISTINCT.
     *
     * @param  string|null ...$column
     * @return Model
     */
    public function selectDistinct(string|null ...$column): Model
    {
        if (empty($column) || (count($column) === 1 && $column[0] === null)) {
            $strSelect = "DISTINCT {$this->table}.* ";
        } else {
            $column = Tools::filter($column, function ($k, $v) {
                if (preg_match("/^[\S]*[ ]{1}[\S]*$/", $v)) {
                    $v = str_replace(' ', ' as ', $v);
                }
                return $v;
            });
            $strSelect = "DISTINCT " . implode(',', $column);
        }

        $this->strQuery = "SELECT {$strSelect}" . $this->fromTable();
        $this->SUB = "SELECT";
        return $this;
    }

    /**
     * Агрегатная функция COUNT.
     *
     * @param  string $field Поле (по умолчанию *)
     * @param  string $alias Псевдоним (по умолчанию count)
     * @return int
     */
    public function count(string $field = '*', string $alias = 'count'): int
    {
        $result = $this->select("COUNT({$field}) {$alias}")->fetch(false);
        return (int)($result[$alias] ?? 0);
    }

    /**
     * Агрегатная функция SUM.
     *
     * @param  string $field
     * @param  string $alias
     * @return float|int
     */
    public function sum(string $field, string $alias = 'sum'): float|int
    {
        $result = $this->select("SUM({$field}) {$alias}")->fetch(false);
        return $result[$alias] ?? 0;
    }

    /**
     * Агрегатная функция AVG.
     *
     * @param  string $field
     * @param  string $alias
     * @return float
     */
    public function avg(string $field, string $alias = 'avg'): float
    {
        $result = $this->select("AVG({$field}) {$alias}")->fetch(false);
        return (float)($result[$alias] ?? 0);
    }

    /**
     * Агрегатная функция MIN.
     *
     * @param  string $field
     * @param  string $alias
     * @return mixed
     */
    public function min(string $field, string $alias = 'min'): mixed
    {
        $result = $this->select("MIN({$field}) {$alias}")->fetch(false);
        return $result[$alias] ?? null;
    }

    /**
     * Агрегатная функция MAX.
     *
     * @param  string $field
     * @param  string $alias
     * @return mixed
     */
    public function max(string $field = 'id', string $alias = 'max'): mixed
    {
        $result = $this->select("MAX({$field}) {$alias}")->fetch(false);
        return $result[$alias] ?? null;
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
     * @param  string $table
     * @param  string $type
     * @return Model
     */
    public function join(string $table, string $type = "LEFT"): Model
    {
        $table = implode(" as ", explode(" ", $table));
        $this->join = "$type JOIN $table ON ";
        $this->SUB = "JOIN";
        return $this;
    }

    /**
     * on
     * Пример 1: "table1.id = table2.table_id"
     * Пример 2: ["table1.id", "table2.table_id", "="]
     * Пример 3: "table1.id = table2.table_id", "AND", ["table1.id", "table2.table_id", "="]
     *
     * @param  string|array ...$select
     * @return Model
     */
    public function on(string|array ...$select): Model
    {
        if ($this->join != '') {
            foreach ($select as $ons) {
                $this->strJoin .= "{$this->join}(";
                if (is_array($ons)) {
                    $sign = $ons[2] ?? "=";
                    $this->strJoin .= $ons[0] . " $sign " . $ons[1];
                }
                if (gettype($ons) == 'string') {
                    $this->strJoin .= $ons;
                }
                $this->strJoin .= ")";
            }
            $this->join = '';
        }
        $this->SUB = "ON";
        return $this;
    }

    /**
     * where
     *
     * @param  string $str
     * @param  string|array $value
     * @param  string $sign
     * @return Model
     */
    public function where(string $str = '', string|array $value = [], string $sign = '='): Model
    {
        if (!empty($value) && gettype($value) == 'string') {
            $str = "$str $sign $value";
        }
        if (!empty($value) && gettype($value) == 'array') {
            $sign = $sign == '=' ? "IN" : $sign;
            $str = "$str $sign (" . implode(',', $value) . ")";
        }

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
        $this->strWhere = empty($where) ? " WHERE $str" : " $where $sign $str";
        $this->SUB = "WHERE";
        return $this;
    }

    /**
     * whereRaw — сырой WHERE без экранирования.
     *
     * @param  string $sql
     * @return Model
     */
    public function whereRaw(string $sql): Model
    {
        return $this->whereAdd($sql);
    }

    /**
     * whereNull
     *
     * @param  string $column
     * @return Model
     */
    public function whereNull(string $column): Model
    {
        return $this->whereAdd("`$column` IS NULL");
    }

    /**
     * whereNotNull
     *
     * @param  string $column
     * @return Model
     */
    public function whereNotNull(string $column): Model
    {
        return $this->whereAdd("`$column` IS NOT NULL");
    }

    /**
     * whereBetween
     *
     * @param  string $column
     * @param  mixed $value1
     * @param  mixed $value2
     * @return Model
     */
    public function whereBetween(string $column, mixed $value1, mixed $value2): Model
    {
        return $this->whereAdd("`$column` BETWEEN '$value1' AND '$value2'");
    }

    /**
     * whereIn
     *
     * @param  string $column
     * @param  array $values
     * @return Model
     */
    public function whereIn(string $column, array $values): Model
    {
        $quoted = $values;
        $this->arrayQuote($quoted);
        return $this->whereAdd("`$column` IN (" . implode(',', $quoted) . ")");
    }

    /**
     * and
     *
     * @param  string $str
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
     * @param  string $str
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
     * @param  string $str
     * @param  string $toSort
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
            $this->strOrders = " ORDER BY $str $toSort";
        }

        return $this;
    }

    /**
     * orderByDesc — сортировка по убыванию.
     *
     * @param  string $str
     * @return Model
     */
    public function orderByDesc(string $str = ""): Model
    {
        return $this->orderBy($str, "DESC");
    }

    /**
     * groupBy
     *
     * @param  string $str
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
     * having — фильтрация после GROUP BY.
     *
     * @param  string $condition
     * @return Model
     */
    public function having(string $condition): Model
    {
        $this->strGroups .= " HAVING $condition";
        $this->SUB = "HAVING";
        return $this;
    }

    /**
     * whereId
     *
     * @param  string|int $id
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
     * @param  string|int $str
     * @return Model
     */
    public function limit(string|int $str = ""): Model
    {
        $this->strLimit .= " LIMIT $str";
        $this->SUB = "LIMIT";
        return $this;
    }

    /**
     * offset
     *
     * @param  string|int $str
     * @return Model
     */
    public function offset(string|int $str = ""): Model
    {
        $this->strOffset = " OFFSET $str";
        $this->SUB = "OFFSET";
        return $this;
    }

    /**
     * page — пагинация (автоматический расчёт OFFSET).
     *
     * @param  int $page Номер страницы (1-based)
     * @param  int $perPage Количество записей на странице
     * @return Model
     */
    public function page(int $page = 1, int $perPage = 20): Model
    {
        $offset = ($page - 1) * $perPage;
        $this->limit($perPage);
        $this->offset($offset);
        return $this;
    }

    /**
     * first — получить первую запись.
     *
     * @return array
     */
    public function first(): array
    {
        $this->limit(1);
        return $this->fetch(false);
    }

    /**
     * findById — найти по ID.
     *
     * @param  int|string $id
     * @return array
     */
    public function findById(int|string $id): array
    {
        $this->whereId($id);
        return $this->fetch(false);
    }

    private function conditions($str, $conds): void
    {
        if ($this->SUB == "WHERE") {
            $this->strWhere .= " $conds $str";
        }
    }

    public function getJoin(): string
    {
        return $this->strJoin;
    }

    public function getWhere(): string
    {
        return $this->strWhere;
    }
}
