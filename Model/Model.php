<?php
namespace Pet\Model;

use Pet\DataBase\DB;
use Pet\DataBase\Delete;
use Pet\DataBase\Update;
use Pet\DataBase\Select;
use Pet\DataBase\Insert;

abstract class Model extends DB
{
    use  Select, Update, Delete, Insert;

    /**
     * find
     *
     * @param  array $searh
     * @param  array $column
     * @param  int $limit
     * @return array
     */
    public function find($searh = [], $column = [], $limit = null): array 
    {
        if ($limit) {
            return  $this->select($column)->And($searh)->limit($limit)->fetch();
        } else {
            return  $this->select($column)->And($searh)->fetch();
        }
    }

    /**
     * isRow
     *
     * @param  mixed $searh
     * @return bool
     */
    public function isRow($searh): bool
    {
        return $this->find($searh, [], 1) != [];
    }


    public function setUp($find, $value)
    {
        if (gettype($find) == 'string') {
            $find = [$find => $value[$find]];
        }
        if ($this->isRow($find)) {
            $this->update($value)->and($find)->fetch();
        } else {
            $this->set($value);
        }
    }

    public function isTable(): bool
    {
        return !empty($this->q("SHOW TABLES FROM `".$this->table."` LIKE 'migrate' ; ")->fetch_array());
    }
}
