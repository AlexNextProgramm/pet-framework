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

    public function __construct(array|int|string|null $data = null, bool $isNotExistCreate = false)
    {
            parent::__construct($data);
        if (!$this->exist() && $isNotExistCreate) {
            if (gettype($data) == 'integer') $data = ['id' => $data];
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
     * @param  int|float|string|bool|null $value
     * @return void
     */
    public function __set(string $name, string|float|int|bool|null $value)
    {
        $this->set($name, $value);
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
            $table =  $this->tableAlias ?: $this->table ;
            $table = !empty($table) ? $table."." : "";
            $fields = Tools::filter($fields, fn($k, $v)=> "{$table}$k = '$v' ");
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
        $class = $this::class;
        return array_map(fn($data)=>(new $class())->setInfo($data), $results);
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
     * @param  array|string $data
     * @param  mixed $value
     * @return bool
     */
    public function set(array|string $data, mixed $value = null): bool
    {
        if (is_string($data)) {
            $data = [$data => $value];
        }
        if (!$this->isInfo() || empty($this->info['id'])){
            throw new AppException("not info in model or not id in info");
        }
        return $this->update($data)->whereId($this->get('id'))->execute();
    }

    /**
     * reboot
     * Перезагрузка значений info
     * @return Model
     */
    public function reboot():Model
    {
        if ($this->exist()) {
            $this->setInfoId((int)$this->get('id'));
        }
        return $this;
    }

    /**
     * exist
     * @param array|null
     * @return bool
     */
    public function exist(?array $data = null):bool
    {
        if (!empty($data)) {
            return !empty($this->find($data, function (Model $m) {
                $m->limit('1');
            }));
        }
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

    /**
     * ifExistDelete
     *
     * @param  array $whereElseId
     * @return bool
     */
    public function ifExistDelete(?array $whereElseId = null):bool {
        $many = [];
        if (!empty($whereElseId)) {
            $many = $this->findM($whereElseId);
        }
        $isBool = false;
        foreach ($many as $model) {
            if ($model->exist()) {
                $model->delete();
                $isBool = true;
            }
        }
        return $isBool;
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

   /**
    * findDelete
    *
    * @param array $params
    * @param callable|null $callback
    * @return array
    */
    public function findDelete(array $params, callable|null $callback = null): array
    {
        $result = $this->find($params, $callback);
        foreach ($result as $r) {
            $class = $this::class;
            $model =  (new $class(['id' => $r['id']]));
            if ($model->exist()) {
                $model->delete();
            }
        }
        return $result;
    }
}
