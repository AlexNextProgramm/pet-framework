<?php

namespace Pet\DataBase;

use Error;
use PDO;
use Exception;
use PDOException;
use PDOStatement;
use Pet\Command\Console\Console;
use Pet\DataBase\Config\DataBase;
use Pet\Errors\AppException;
use Pet\Tools\Tools;

abstract class DB
{
    /**
     * @var string Имя подключения к БД (для мульти-БД)
     */
    protected string $connectionName = 'default';

    /**
     * @var string|null Тип БД (mysql, pgsql, sqlite) — определяется из конфига
     */
    protected ?string $db_type = null;

    /**
     * @var string|null Хост
     */
    protected ?string $db_host = null;

    /**
     * @var string Имя БД
     */
    protected string $db_name = '';

    /**
     * @var string|int|null Порт
     */
    protected string|int|null $db_port = null;

    /**
     * @var string|null Пользователь
     */
    protected ?string $db_user = null;

    /**
     * @var string|null Пароль
     */
    protected ?string $db_password = null;

    protected string $strQuery = "";
    protected string $strWhere = "";
    protected string $strOrders = "";
    protected string $strJoin = "";
    protected string $strGroups = "";
    protected string $strLimit = "";
    protected string $strOffset = "";

    protected $SUB = "";

    protected $info = [];

    protected string $table = "";
    protected string|false $tableAlias = false;
    protected $column = [];
    protected $error = [];
    private PDO|null $DB = null;

    /**
     * Устанавливает имя подключения к БД.
     * Позволяет переключаться между разными базами данных.
     *
     * @param string $name Имя подключения из конфига
     * @return static
     */
    public function setConnection(string $name): static
    {
        $this->connectionName = $name;
        $this->DB = null; // Сбросим PDO, чтобы переподключиться
        return $this;
    }

    /**
     * Возвращает имя текущего подключения.
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    protected function pdo(): PDO
    {
        if ($this->DB === null) {
            $this->DB = ConnectionManager::connection($this->connectionName);
            // Синхронизируем свойства с конфигом
            $config = DataBase::get($this->connectionName);
            $this->db_type = $config['type'];
            $this->db_host = $config['host'];
            $this->db_name = $config['name'];
            $this->db_port = $config['port'];
            $this->db_user = $config['user'];
            $this->db_password = $config['password'];
        }
        return $this->DB;
    }

    /**
     * __construct
     *
     * @param array|int|string|null $id
     * @param string|null $connectionName Имя подключения (для мульти-БД)
     * @return void
     */
    public function __construct(array|int|string|null $id = null, ?string $connectionName = null)
    {
        if ($connectionName !== null) {
            $this->setConnection($connectionName);
        }
        $this->pdo(); // Инициализация подключения
        $this->setInfoId($id);
    }

    /**
     * fetch
     *
     * @return array
     */
    public function fetch($many = true) : array
    {
        try {
            $query = $this->toString();
            $this->clearQuery();
            if ($many) {
                return $this->q($query)->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } else {
                return $this->q($query)->fetch(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (PDOException|Exception $q) {
            $this->error[] = $q->errorInfo ?? $q->getMessage();
            throw new AppException($q->errorInfo[2] ?? $q->getMessage(), $q->errorInfo[1] ?? $q->getCode());
            return [];
        }
    }

    public function toString(): string
    {
        return $this->strQuery . $this->strJoin . $this->strWhere . $this->strGroups . $this->strOrders . $this->strLimit . $this->strOffset;
    }

    private function clearQuery(): void
    {
        $this->strQuery = $this->strJoin = $this->strWhere = $this->strGroups = $this->strOrders = $this->strOffset = $this->strLimit = '';
    }

     /**
     * execute
     *
     * @return bool
     */
    public function execute(): bool
    {
        try {
            $query = $this->toString();
            $this->clearQuery();
            return $this->pdo()->prepare($query)->execute();
        } catch (PDOException $q) {
            $this->error[] = $q->errorInfo;
            throw new AppException($q->errorInfo[2] ?? $q->getMessage(), $q->errorInfo[1] ?? $q->getCode());
            return false;
        }
    }

    /**
     * conn — больше не используется напрямую.
     * Подключение управляется через ConnectionManager::connection()
     *
     * @deprecated Используйте $this->pdo()
     */
    private function conn(): void
    {
        $this->pdo();
    }

    /**
     * FromTable
     *
     * @param  mixed $from
     * @return string
     */
    public function FromTable(string $from = "FROM"): string
    {
       return " $from `{$this->table}` ".($this->tableAlias ? " AS {$this->tableAlias} " : "");
    }

    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * q
     *
     * @param  mixed $query
     * @return PDOStatement
     */
    public function q(string $query): PDOStatement
    {
        $pdo = $this->pdo();
        if (!$pdo) {
            throw new AppException('NO CONNECT DB');
        }
        return $pdo->query($query, PDO::FETCH_ASSOC);
    }

    /**
     * setInfoId
     *
     * @param  mixed $id
     * @return void
     */
    public function setInfoId(mixed $id): void
    {
        if (empty($id)) {
            return;
        }
        $pdoStatment = null;
        $query = '';
        if (gettype($id) == 'string' || gettype($id) == 'integer') {
            $query = "SELECT * FROM {$this->table} WHERE {$this->table}.id = '$id';";
            $pdoStatment = $this->q($query);
        }

        if (gettype($id) == 'array') {
            $data = implode(" AND ", Tools::filter($id, fn($k, $v) => "{$this->table}.$k = '$v' "));
            $query = "SELECT * FROM {$this->table} WHERE $data LIMIT 2;";
            $pdoStatment =  $this->q($query);
        }
        $result = $pdoStatment ? $pdoStatment->fetchAll(PDO::FETCH_ASSOC) : [];
        if (count($result) > 1) {
            throw new AppException('Модель не может присвоить множество ваш запрос получает более 2 строк ' . $query);
        }
        $this->info = count($result) == 1 ? $result[0] : [];
    }

    /**
     * isInfo
     *
     * @return bool
     */
    public function isInfo(): bool
    {
        return !empty($this->info);
    }

    /**
     * get
     *
     * @param  string|int $field
     * @return string|null
     */
    public function get(string|int $field): string|null
    {
        if ($this->isInfo()) {
            return $this->info[$field] ?? null;
        }
        return null;
    }

    public function arrayQuote(&$array): void
    {
        $pdo = $this->pdo();
        foreach ($array as $i => $v) {
            $array[$i] = $pdo->quote($v);
        }
    }

    public function endError(): null
    {
        return $this->error[array_key_last($this->error)] ?? null;
    }

    public function getInfo(): array
    {
        return $this->info ?? [];
    }

    /**
     * Возвращает lastInsertId от текущего PDO-подключения.
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo()->lastInsertId();
    }

    /**
     * Возвращает имя текущей БД.
     *
     * @return string
     */
    public function getDbName(): string
    {
        return ConnectionManager::getDbName($this->connectionName);
    }

    /**
     * Начинает транзакцию.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo()->beginTransaction();
    }

    /**
     * Подтверждает транзакцию.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo()->commit();
    }

    /**
     * Откатывает транзакцию.
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->pdo()->rollBack();
    }

    /**
     * Проверяет, активна ли транзакция.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo()->inTransaction();
    }
}
