<?php

namespace Pet\DataBase;

use PDOException;

trait Insert
{

    /**
     * insert
     *
     * @param  array $ArrayColumnAndValue
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
        return $this->execute();
    }

    /**
     * create — вставка с возвратом ID и установкой info.
     *
     * @param  array $data
     * @return string|false ID вставленной записи или false
     */
    public function create(array $data): string|false
    {
        if ($this->insert($data)) {
            $id = $this->lastInsertId();
            $this->setInfoId($id);
            return $id;
        }
        return false;
    }

    /**
     * insertBatch — массовая вставка нескольких записей.
     *
     * @param  array $rows Массив строк, каждая строка — ассоциативный массив
     * @return bool
     */
    public function insertBatch(array $rows): bool
    {
        if (empty($rows)) {
            return false;
        }

        $keys = array_keys(reset($rows));
        $columns = implode(' , ', $keys);
        $values = [];

        foreach ($rows as $row) {
            $vals = array_values($row);
            $this->arrayQuote($vals);
            $values[] = "( " . implode(", ", $vals) . " )";
        }

        $this->strQuery = "INSERT INTO {$this->table} ( $columns ) VALUES " . implode(', ', $values);
        $this->SUB = "INSERT_BATCH";
        return $this->execute();
    }

    /**
     * insertOnDuplicate — вставка с ON DUPLICATE KEY UPDATE.
     *
     * @param  array $data Данные для вставки
     * @param  array|null $update Поля для обновления (null = все поля)
     * @return bool
     */
    public function insertOnDuplicate(array $data, ?array $update = null): bool
    {
        if (empty($data)) {
            return false;
        }

        $key = array_keys($data);
        $value = array_values($data);
        $this->arrayQuote($value);

        $columns = implode(' , ', $key);
        $values = implode(", ", $value);

        if ($update === null) {
            $update = $key;
        }

        $updates = [];
        foreach ($update as $field) {
            $updates[] = "`$field` = VALUES(`$field`)";
        }

        $this->strQuery = "INSERT INTO {$this->table} ( $columns ) VALUES ( $values ) ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        $this->SUB = "INSERT_ON_DUPLICATE";
        return $this->execute();
    }

    /**
     * replace — REPLACE INTO (MySQL).
     *
     * @param  array $data
     * @return bool
     */
    public function replace(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $key = array_keys($data);
        $value = array_values($data);
        $this->arrayQuote($value);

        $this->strQuery = "REPLACE INTO {$this->table} ( " . implode(' , ', $key) . " ) VALUES ( " . implode(", ", $value) . ")";
        $this->SUB = "REPLACE";
        return $this->execute();
    }
}
