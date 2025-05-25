<?php

namespace Pet\DataBase;

use PDOException;

trait Insert
{

  /**
   * insert
   *
   * @param  array $ArrayColumnAndValue
   * @param  mixed $returnThis
   * @return bool
   */
    public function insert(array $ArrayColumnAndValue = []): bool
    {

        if (count($ArrayColumnAndValue) == 0) {
            return false;
        }
        $key = array_keys($ArrayColumnAndValue);
        $value = array_values($ArrayColumnAndValue);
        $this->arrayQuote($value);
        $this->strQuery = "INSERT INTO {$this->table} ( " . implode(' , ', $key) . " ) VALUES ( " . implode(", ", $value) . ")";
        $this->SUB = "INSERT";
        return  $this->execute();
    }

    public function create($data) {
        if ($this->insert($data)) {
            $id = $this->pdo()->lastInsertId();
            $this->setInfoId($id);
            return $id;
        }
    }
}
