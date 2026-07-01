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
    use Select, Update, Delete, Insert;

    public array $hidden = [];

    /**
     * @var bool Флаг проверки на множественный результат при загрузке модели.
     * Если false (по умолчанию) — при нахождении нескольких строк берётся первая.
     * Если true — выбрасывается исключение, если найдено более 1 строки.
     */
    protected bool $allowMultiple = false;

    /**
     * @var string|null Имя подключения к БД для этой модели
     */
    protected static ?string $connection = null;

    /**
     * @var string|null Таблица для модели (может быть переопределена в наследнике)
     */
    protected static ?string $tableName = null;

    public function __construct(array|int|string|null $data = null, bool $isNotExistCreate = false, ?string $connectionName = null)
    {
        // Если указано статическое подключение для модели
        if ($connectionName !== null) {
            $this->setConnection($connectionName);
        } elseif (static::$connection !== null) {
            $this->setConnection(static::$connection);
        }

        parent::__construct($data, $this->connectionName);

        if (!$this->exist() && $isNotExistCreate) {
            if (gettype($data) == 'integer') $data = ['id' => $data];
            $this->create($data);
        }
    }

    /**
     * Устанавливает имя таблицы для модели.
     *
     * @param string $table
     * @return static
     */
    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Устанавливает псевдоним таблицы.
     *
     * @param string $alias
     * @return static
     */
    public function setTableAlias(string $alias): static
    {
        $this->tableAlias = $alias;
        return $this;
    }

    /**
     * __get
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->info[$name] ?? null;
    }

    /**
     * __set
     *
     * @param  string $name
     * @param  string|float|int|bool|null $value
     * @return void
     */
    public function __set(string $name, string|float|int|bool|null $value): void
    {
        $this->set($name, $value);
    }

    /**
     * find
     *
     * @param  array|null $fields
     * @param  callable|null $callback
     * @return array
     */
    public function find(array|null $fields = null, callable|null $callback = null): array
    {
        $this->select();
        if ($fields) {
            $table = $this->tableAlias ?: $this->table;
            $table = !empty($table) ? $table . "." : "";
            $fields = Tools::filter($fields, fn($k, $v) => "{$table}$k = '$v' ");
            $this->where(implode(' AND ', $fields));
        }
        if ($callback) {
            $callback($this);
        }
        return $this->fetch();
    }

    /**
     * findM
     *
     * @param  mixed $fields
     * @param  mixed $callback
     * @return array
     */
    public function findM(array|null $fields = null, callable|null $callback = null): array
    {
        $results = $this->find($fields, $callback);
        $class = $this::class;
        return array_map(fn($data) => (new $class())->setInfo($data), $results);
    }

    /**
     * findAll — получить все записи из таблицы.
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->select()->fetch();
    }

    /**
     * findBy — найти по полю и значению.
     *
     * @param  string $field
     * @param  mixed $value
     * @param  string $sign
     * @return array
     */
    public function findBy(string $field, mixed $value, string $sign = '='): array
    {
        return $this->select()->where($field, $value, $sign)->fetch();
    }

    /**
     * findByM — найти по полю и значению, вернуть массив моделей.
     *
     * @param  string $field
     * @param  mixed $value
     * @param  string $sign
     * @return array
     */
    public function findByM(string $field, mixed $value, string $sign = '='): array
    {
        $results = $this->findBy($field, $value, $sign);
        $class = $this::class;
        return array_map(fn($data) => (new $class())->setInfo($data), $results);
    }

    /**
     * pluck — получить массив значений одного поля.
     *
     * @param  string $column
     * @return array
     */
    public function pluck(string $column): array
    {
        $results = $this->select($column)->fetch();
        return array_map(fn($row) => $row[$column] ?? null, $results);
    }

    /**
     * chunk — обработка записей частями.
     *
     * @param  int $size
     * @param  callable $callback
     * @return void
     */
    public function chunk(int $size, callable $callback): void
    {
        $page = 1;
        do {
            $this->clearQuery();
            $results = $this->select()->page($page, $size)->fetch();
            if (empty($results)) break;
            $callback($results);
            $page++;
        } while (count($results) === $size);
    }

    /**
     * setInfo
     *
     * @param  array $data
     * @return Model
     */
    private function setInfo(array $data): Model
    {
        $this->info = $data;
        return $this;
    }

    /**
     * isTable
     *
     * @return bool
     */
    public function isTable(): bool
    {
        return !empty($this->q("SHOW TABLES FROM `" . $this->getDbName() . "` LIKE 'migrate' ; ")->fetch());
    }

    /**
     * set
     *
     * @param  array|string $data
     * @param  mixed $value
     * @return bool
     */
    public function set(array|string $data, mixed $value = null): bool
    {
        if (is_string($data)) {
            $data = [$data => $value];
        }
        if (!$this->isInfo() || empty($this->info['id'])) {
            throw new AppException("not info in model or not id in info");
        }
        return $this->update($data)->whereId($this->get('id'))->execute();
    }

    /**
     * reboot
     *
     * @return Model
     */
    public function reboot(): Model
    {
        if ($this->exist()) {
            $this->setInfoId((int)$this->get('id'));
        }
        return $this;
    }

    /**
     * exist
     *
     * @param  array|null $data
     * @return bool
     */
    public function exist(?array $data = null): bool
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
     *
     * @param  array $data
     * @param  array|int|string|null $whereElseId
     * @return Model
     */
    public function ifExistSetOrCreate(array $data, array|int|string|null $whereElseId = null): Model
    {
        if (!empty($whereElseId)) {
            $this->setInfoId($whereElseId);
        } else {
            if (($data['id'] ?? false)) {
                $this->setInfoId((int)$data['id']);
                unset($data['id']);
            }
        }
        if ($this->exist()) {
            $this->set($data);
        } else {
            $this->create($data);
        }

        return $this;
    }

    /**
     * ifExistDelete
     *
     * @param  array|null $whereElseId
     * @return bool
     */
    public function ifExistDelete(?array $whereElseId = null): bool
    {
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
     *
     * @return array
     */
    public function data(): array
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
     * @param  array $params
     * @param  callable|null $callback
     * @return array
     */
    public function findDelete(array $params, callable|null $callback = null): array
    {
        $result = $this->find($params, $callback);
        foreach ($result as $r) {
            $class = $this::class;
            $model = (new $class(['id' => $r['id']]));
            if ($model->exist()) {
                $model->delete();
            }
        }
        return $result;
    }

    /**
     * fresh — обновить info из БД.
     *
     * @return static
     */
    public function fresh(): static
    {
        if ($this->exist()) {
            $this->setInfoId((int)$this->get('id'));
        }
        return $this;
    }

    /**
     * toArray — преобразовать модель в массив.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data();
    }

    /**
     * Создаёт новую запись и возвращает модель.
     *
     * @param  array $data
     * @return static|null
     */
    public static function createNew(array $data): ?static
    {
        $model = new static();
        $id = $model->create($data);
        return $id ? $model : null;
    }
}
