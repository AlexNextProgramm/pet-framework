<?php
namespace Pet\DataBase;

class Insert extends Select{

  /**
   * insert
   *
   * @param  mixed $ArrayColumnAndValue
   * @param  mixed $returnThis
   * @return bool
   */
  function insert(array $ArrayColumnAndValue = [], $returnThis = false): bool|Insert {
    $key = array_keys($ArrayColumnAndValue);
    $value = array_values($ArrayColumnAndValue);
    $this->strQuery = "INSERT INTO `{$this->table}` ( " . implode(' , ', $key) . " ) VALUES ( '" . implode("', '", $value) . "')";
    return $returnThis ? $this : $this->DB->prepare($this->strQuery)->execute();
  }

  function execute() {
    return $this->DB->prepare($this->strQuery)->execute();
  }
}
?>