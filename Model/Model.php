<?php
namespace Pet\Model;

use Pet\DataBase\DB;
use Pet\DataBase\Delete;
use Pet\DataBase\Update;
use Pet\DataBase\Select;
use Pet\DataBase\Insert;
use Pet\Tools\Tools;

abstract class Model extends DB
{
    use  Select, Update, Delete, Insert;
    public array $hidden = [];

    public function __construct(array|null $data = null, bool $isNotExistCreate = false)
    {
            parent::__construct($data);
        if (!$this->exist() && $isNotExistCreate) {
            $this->create($data);
        }
    }

    public function find($fields = null, callable|null $callback = null) : array
    {
        $this->select();
        if ($fields) {
            $table =  $this->tableAlias ?? $this->table ;
            $fields = Tools::filter($fields, fn($k, $v)=> "{$table}.$k = '$v' ");
            $this->where(implode(' AND ', $fields));
        }
        if ($callback) {
            $callback($this);
        }
        return $this->fetch();
    }

    public function isTable(): bool
    {
        return !empty($this->q("SHOW TABLES FROM `".$this->db_name."` LIKE 'migrate' ; ")->fetch());
    }

    /**
     * set
     *
     * @param  mixed $data
     * @return bool
     */
    public function set(array $data): bool
    {
        if ($this->isInfo()) {
            return $this->update($data)->whereId($this->get('id'))->execute();
        }
        return false;
    }

    /**
     * exist
     *
     * @return bool
     */
    public function exist():bool
    {
        return $this->isInfo();
    }

    /**
     * data
     *
     * @return array
     */
    public function data() : array
    {
        $result = [];
        if ($this->isInfo()) {
            $result = Tools::is_assos($this->info) === 'assos' ? $this->info : $this->info[0];
            foreach ($this->hidden as $col) {
                unset($result[$col]);
            }
        }
        return $result;
    }
}
