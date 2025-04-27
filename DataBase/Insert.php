<?php

namespace Pet\DataBase;

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
        $this->strQuery = "INSERT INTO `{$this->table}` ( " . implode(' , ', $key) . " ) VALUES ( " . implode(", ", $value) . ")";

        return  $this->DB->prepare($this->strQuery)->execute();
    }

    /**
     * execute
     *
     * @return bool
     */
    public function execute(): bool
    {
        return $this->DB->prepare($this->strQuery)->execute();
    }


    /**
     * set
     *  вернет последний id после установки значения
     * @param  mixed $value
     * @return string
     */
    public function set(array $value): string {
        $this->insert($value);
        return $this->DB->lastInsertId();
    }
}
