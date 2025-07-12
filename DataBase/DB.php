<?php

namespace Pet\DataBase;

use Error;
use PDO;
use Exception;
use PDOException;
use PDOStatement;
use Pet\Command\Console\Console;
use Pet\Errors\AppException;
use Pet\Tools\Tools;

abstract class DB
{

    private string $db_type = DB_TYPE;
    private string $db_host = DB_HOST;
    protected string $db_name = DB_NAME;
    private string|int $db_port = DB_PORT;
    private string $db_user = DB_USER;
    private string $db_password = DB_PASSWORD;


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

    protected function pdo() {
        return $this->DB;
    }
    /**
     * __construct
     *
     * @param array|string $id
     * @return void
     */
    public function __construct(array|string|null $id = null)
    {
        $this->conn();
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
            $this->error[] = $q->errorInfo;
            throw new AppException($q->errorInfo[2], $q->errorInfo[1]);
            return [];
        }
    }

    public function toString(): string
    {
        return $this->strQuery . $this->strJoin . $this->strWhere . $this->strGroups . $this->strOrders.$this->strLimit. $this->strOffset;
    }

    private function clearQuery():void
    {
        $this->strQuery = $this->strJoin = $this->strWhere =  $this->strGroups = $this->strOrders = $this->strOffset = $this->strLimit = '';
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
            return $this->DB->prepare($query)->execute();
        } catch (PDOException $q) {
            $this->error[] = $q->errorInfo;
            throw new AppException($q->errorInfo[2], $q->errorInfo[1]);
            return false;
        }
    }


    private function conn()
    {
        try {
            $this->DB = new PDO("{$this->db_type}:host={$this->db_host}:{$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_password);
        } catch (\PDOException $e) {
            throw new AppException($e->errorInfo[2], $e->errorInfo[1]);
        }
    }

    /**
     * FromTable
     *
     * @param  mixed $from
     * @return string
     */
    public function FromTable(string $from = "FROM"):string
    {
       return " $from `{$this->table}` ".($this->tableAlias? " AS {$this->tableAlias} ": "");
    }
    public function getTableName(){
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
        if (!$this->DB) {
            throw new AppException('NO CONNECT DB');
        }
        return $this->DB->query($query, PDO::FETCH_ASSOC);
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
        if (gettype($id) == 'string' || gettype($id) == 'integer') {
            $pdoStatment = $this->q("SELECT * FROM {$this->table} WHERE id='$id';");
        }

        if (gettype($id) == 'array') {
            $data = implode(" AND ", Tools::filter($id, fn($k, $v) => "{$this->table}.$k = '$v' "));
            $pdoStatment =  $this->q("SELECT * FROM {$this->table} WHERE $data LIMIT 2;");
        }
        $result = $pdoStatment ? $pdoStatment->fetchAll(PDO::FETCH_ASSOC):[];
            $this->info = count($result) == 1 ? $result[0] : []; // не должно в этом условии быть множества
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
        if (!$this->DB) {
            $this->conn();
        }
        foreach ($array as $i => $v) {
            $array[$i] = $this->DB->quote($v);
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
}
