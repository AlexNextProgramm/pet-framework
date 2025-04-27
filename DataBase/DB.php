<?php

namespace Pet\DataBase;

use PDO;
use Exception;
use PDOStatement;
use Pet\Command\Console\Console;
use Pet\Tools\Tools;

abstract class DB {

    private $db_type = DB_TYPE;
    private $db_host = DB_HOST;
    protected $db_name = DB_NAME;
    private $db_port = DB_PORT;
    private $db_user = DB_USER;
    private $db_password = DB_PASSWORD;


    public $strQuery = "";
    public $strWhere = "";
    public $isSoftRemoval = false;
    public $strJoin = '';
    protected $info = [];
    public $table;
    public $tableChanged;
    public $column = [];
    public PDO|null $DB = null;

    /**
     * __construct
     *
     * @param array|string $id
     * @return void
     */
    public function __construct(array|string|null $id = null) {
        $this->conn();
        $this->info = $this->get($id);
    }

    /**
     * fetch
     *
     * @return array
     */
    public function fetch()
    {
        $this->complecte();
        $this->info = $this->q($this->strQuery)->fetchAll(PDO::FETCH_ASSOC);
        return $this->info;
    }

    public function complecte()
    {
        $this->strQuery = $this->strQuery . $this->strJoin . $this->strWhere;
        $this->strJoin  = $this->strJoin = $this->strWhere = '';
        $this->whereSyntax($this->strQuery);
    }

     /**
     * execute
     *
     * @return bool
     */
    public function execute(): bool
    {
        $this->complecte();
        return $this->DB->prepare($this->strQuery)->execute();
    }


    private function conn() {
        try {
            $this->DB = new PDO("{$this->db_type}:host={$this->db_host}:{$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_password);
        } catch (\PDOException $e) {
            Console::text("ERROR DB: " . $e->getMessage(), 'red');
        }
    }


    public function q($query): PDOStatement|null
    {
        if ($this->DB != null) {
            return $this->DB->query($query, PDO::FETCH_ASSOC);
        } else {
            Console::text("NOT CONNECT DB", 'red');
            exit;
        }
    }



    private function whereSyntax(&$query)
    {
        $str =  explode('WHERE', $query);
        if (count($str) == 2 && trim($str[1]) == '') {
            $query = $str[0] . 'WHERE 1';
        } else {
            $str = explode('ORDER BY', $str[1]);
            if (count($str) == 2 && trim($str[0]) == '') {
                $query = str_replace('WHERE', '', $query);
            }
        }
    }


    private function get($id): array
    {
        if (empty($id)) {
            return [];
        }
        $pdoStatment = null;
        if (gettype($id) == 'string' || gettype($id) == 'integer') {
            $pdoStatment = $this->q("SELECT * FROM {$this->table} WHERE id='$id';");
        }

        if (gettype($id) == 'array' && !empty($id)) {
            $ids = !empty($id['id']) ? $id['id'] : null;
            if ($ids) {
                $pdoStatment =  $this->q("SELECT * FROM {$this->table} WHERE id='$ids';");
            } else {
                $where = Tools::array_implode(", AND ", $id, "[key]='[val]'");
                $pdoStatment =  $this->q("SELECT * FROM {$this->table} WHERE $where;");
            }
        }
        $result = $pdoStatment->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) == 1) {
            return  $result[0];
        }
        return [];
    }

    public function isInfo(): bool
    {
        return !empty($this->info);
    }

    public function v($key): string|null
    {
        if ($this->isInfo()) {
            Tools::is_assos($this->info) ? $this->info[$key] : $this->info[0][$key];
        }
        return null;
    }

    public function arrayQuote(&$array)
    {
        foreach ($array as $i => $v) {
            $array[$i] = $this->DB->quote($v);
        }
    }

    public function esi
}
