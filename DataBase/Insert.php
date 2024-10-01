<?php

namespace Pet\DataBase;

class Insert extends Select {

  /**
   * insert
   *
   * @param  array $ArrayColumnAndValue
   * @param  mixed $returnThis
   * @return bool
   */
  public function insert(array $ArrayColumnAndValue = []): bool {

    if (count($ArrayColumnAndValue) == 0) return false;
    $key = array_keys($ArrayColumnAndValue);
    $value = array_values($ArrayColumnAndValue);
    $this->strQuery = "INSERT INTO `{$this->table}` ( " . implode(' , ', $key) . " ) VALUES ( '" . implode("', '", $value) . "')";
    return  $this->DB->prepare($this->strQuery)->execute();
  }

  /**
   * execute
   *
   * @return bool
   */
  public function execute(): bool {
    return $this->DB->prepare($this->strQuery)->execute();
  }
}
