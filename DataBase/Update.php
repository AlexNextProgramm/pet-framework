<?php

namespace Pet\DataBase;

use Pet\DataBase\Delete;
use Pet\Model\Model;
use Pet\Tools\Tools;

trait Update
{
    /**
     * update
     *
     * @param  array $arrayKeyAndValue
     * @return Model
     */
    public function update(array $arrayKeyAndValue): Model
    {
        $this->arrayQuote($arrayKeyAndValue);
        $str = Tools::array_implode(',', $arrayKeyAndValue, "`[key]`=[val]");
        $table = $this->getTableName();
        $this->strQuery = "UPDATE `$table` SET $str";
        $this->SUB = "UPDATE";
        return $this;
    }

    /**
     * updateBatch — массовое обновление с CASE.
     *
     * @param  array $rows Массив строк с ключами
     * @param  string $keyField Поле-идентификатор (по умолчанию id)
     * @return bool
     */
    public function updateBatch(array $rows, string $keyField = 'id'): bool
    {
        if (empty($rows)) {
            return false;
        }

        $table = $this->getTableName();
        $keys = array_keys(reset($rows));
        $ids = [];

        $cases = [];
        foreach ($keys as $field) {
            if ($field === $keyField) continue;
            $caseSql = "`$field` = CASE ";
            foreach ($rows as $row) {
                $id = $row[$keyField];
                $val = $row[$field];
                $ids[] = $id;
                $caseSql .= "WHEN `$keyField` = " . (is_string($id) ? "'$id'" : $id) . " THEN " . (is_string($val) ? "'$val'" : $val) . " ";
            }
            $caseSql .= "END";
            $cases[] = $caseSql;
        }

        $ids = array_unique($ids);
        $idList = implode(',', array_map(fn($id) => is_string($id) ? "'$id'" : $id, $ids));

        $this->strQuery = "UPDATE `$table` SET " . implode(', ', $cases) . " WHERE `$keyField` IN ($idList)";
        $this->SUB = "UPDATE_BATCH";
        return $this->execute();
    }

    /**
     * increment — увеличить поле на значение.
     *
     * @param  string $field
     * @param  int|float $amount
     * @return Model
     */
    public function increment(string $field, int|float $amount = 1): Model
    {
        $table = $this->getTableName();
        $this->strQuery = "UPDATE `$table` SET `$field` = `$field` + $amount";
        $this->SUB = "UPDATE";
        return $this;
    }

    /**
     * decrement — уменьшить поле на значение.
     *
     * @param  string $field
     * @param  int|float $amount
     * @return Model
     */
    public function decrement(string $field, int|float $amount = 1): Model
    {
        $table = $this->getTableName();
        $this->strQuery = "UPDATE `$table` SET `$field` = `$field` - $amount";
        $this->SUB = "UPDATE";
        return $this;
    }
}
