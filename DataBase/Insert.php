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
        $from = $this->fromTable("");
        $this->strQuery = "INSERT INTO $from ( " . implode(' , ', $key) . " ) VALUES ( " . implode(", ", $value) . ")";
        $this->SUB = "INSERT";
        return  $this->execute();
    }

    public function create($data)
    {
            $this->insert($data);
            return $this->DB->lastInsertId();
    }
}
