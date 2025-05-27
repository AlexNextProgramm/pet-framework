<?php
namespace Pet\Model;

use Pet\DataBase\DB;
use Pet\DataBase\Delete;
use Pet\DataBase\Update;
use Pet\DataBase\Select;
use Pet\DataBase\Insert;
use Pet\Errors\AppException;
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

    /**
     * __get
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->info[$name];
    }

    /**
     * __set
     * при установки значений закидываем в таблицу update
     * @param  string $name
     * @param  int|float|string|bool $value
     * @return void
     */
    public function __set(string $name, int|float|string|bool $value)
    {
        if (!$this->isInfo() || empty($this->info['id'])){
            throw new AppException("not info in model or not id in info");
        }
       return $this->update([$name => $value])->whereId($this->info['id'])->fetch();
    }

    /**
     * find
     * Возвращает массив
     * @param  array|null $fields
     * @param  callable|null $callback
     * @return array
     */
    public function find(array|null $fields = null, callable|null $callback = null) : array
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

    /**
     * findM
     * Возвразает массив моделей
     * @param  mixed $fields
     * @param  mixed $callback
     * @return array
     */
    public function findM(array|null $fields = null, callable|null $callback = null): array
    {
        $results = $this->find($fields, $callback);
        return array_map(fn($data)=>(new self())->setInfo($data), $results);
    }

    /**
     * setInfo
     * устанавливет info только для модели 
     * @param array $data
     * @return Model
     */
    private function setInfo(array $data):Model
    {
        $this->info = $data;
        return $this;
    }

    /**
     * isTable
     * @return bool
     */
    public function isTable(): bool
    {
        return !empty($this->q("SHOW TABLES FROM `".$this->db_name."` LIKE 'migrate' ; ")->fetch());
    }

    /**
     * set
     * принимает ассоциативный массив
     * @param  array $data
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
     * reboot
     * Перезагрузка значений info
     * @return Model
     */
    public function reboot():Model
    {
        if($this->exist()){
            $this->setInfoId((int)$this->get('id'));
        }
        return $this;
    }

    /**
     * exist
     * @return bool
     */
    public function exist():bool
    {
        return $this->isInfo();
    }

    /**
     * ifExistSetOrCreate
     * Создать если не сущестнцует
     * @param  mixed $data
     * @param  mixed $where
     * @return void
     */
    public function ifExistSetOrCreate(array $data, array|int|string|null $whereElseId = null): Model
    {
        if (!empty($whereElseId)) {
            $this->setInfoId($whereElseId);
        } else {
            if (($data['id'] ?? false)) {
                $this->setInfoId((int) $data['id']);
                unset($data['id']);
            } else {
                $this->setInfoId($data);
            }
        }
        if ($this->exist()) {
            $this->set($data);
        }else{
            $this->create($data);
        }

        return $this;
    }

    public function ifExistDelete(array|int|string|null $whereElseId = null):bool {
        if(!empty($whereElseId)){
            $this->setInfo($whereElseId);
        }

        if ($this->exist()) {
            $this->delete();
            return true;
        }
        return false;
    }

    /**
     * data
     * одает и обрабатывает скрытые поля
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
