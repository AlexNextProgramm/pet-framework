<?php

namespace Pet\DataBase;

use Pet\DataBase\Insert;
use Pet\Model\Model;
use Pet\Tools\Tools;

trait Delete
{
    /**
     * delete — удаление текущей записи (если есть info) или построитель DELETE.
     *
     * @return Model
     */
    public function delete(): Model
    {
        $this->strQuery = "DELETE FROM {$this->table}";
        $this->SUB = "DELETE";
        if ($this->isInfo()) {
            $this->whereId($this->get('id'));
            $this->execute();
        }
        return $this;
    }

    /**
     * deleteById — удаление по ID.
     *
     * @param  int|string $id
     * @return bool
     */
    public function deleteById(int|string $id): bool
    {
        $this->strQuery = "DELETE FROM {$this->table}";
        $this->whereId($id);
        return $this->execute();
    }

    /**
     * deleteWhere — удаление с условием.
     *
     * @param  string $field
     * @param  mixed $value
     * @param  string $sign
     * @return bool
     */
    public function deleteWhere(string $field, mixed $value, string $sign = '='): bool
    {
        $this->strQuery = "DELETE FROM {$this->table}";
        $this->where($field, $value, $sign);
        return $this->execute();
    }

    /**
     * truncate — очистка таблицы.
     *
     * @return bool
     */
    public function truncate(): bool
    {
        $this->strQuery = "TRUNCATE TABLE {$this->table}";
        $this->SUB = "TRUNCATE";
        return $this->execute();
    }

    /**
     * softDelete — устанавливает поле deleted_at в текущую дату.
     * Для использования требуется поле deleted_at в таблице.
     *
     * @return Model
     */
    public function softDelete(): Model
    {
        $table = $this->getTableName();
        $this->strQuery = "UPDATE `$table` SET `deleted_at` = NOW()";
        $this->SUB = "UPDATE";
        if ($this->isInfo()) {
            $this->whereId($this->get('id'));
            $this->execute();
        }
        return $this;
    }

    /**
     * restore — восстанавливает soft-deleted запись.
     *
     * @return Model
     */
    public function restore(): Model
    {
        $table = $this->getTableName();
        $this->strQuery = "UPDATE `$table` SET `deleted_at` = NULL";
        $this->SUB = "UPDATE";
        if ($this->isInfo()) {
            $this->whereId($this->get('id'));
            $this->execute();
        }
        return $this;
    }
}
