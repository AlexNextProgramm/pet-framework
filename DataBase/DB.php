<?php

namespace Pet\DataBase;

use PDO;
use Exception;
use PDOStatement;
use Pet\Tools\Tools;

abstract class DB {

    private  $db_type = DB_TYPE;
    private  $db_host = DB_HOST;
    private  $db_name = DB_NAME;
    private  $db_port = DB_PORT;
    private  $db_user = DB_USER;
    private  $db_password = DB_PASSWORD;


    public $strQuery = "";
    public $strWhere = "";
    public $isSoftRemoval = false;
    public $strJoin = '';
    public $info = [];
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
    public function __construct(array|string $id = null) {
        $this->conn();
        $this->info = $this->get($id);
    }

    /**
     * fetch
     *
     * @return array
     */
    public function fetch(){
        $this->strQuery = $this->strQuery . $this->strJoin . $this->strWhere;
        $this->strJoin  = $this->strJoin = $this->strWhere = '';
        $this->whereSyntax($this->strQuery);
        return $this->q($this->strQuery)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function conn(){
        try {
            $this->DB = new PDO("{$this->db_type}:host={$this->db_host}:{$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_password);
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }


    public function q($query):PDOStatement|null
    {
        if($this->DB != null){
            return $this->DB->query($query, PDO::FETCH_ASSOC);
        }else{
            die('NOT CONNECT DB');
        }
    }



    private function whereSyntax(&$query) {
        $str =  explode('WHERE', $query);
        if (count($str) == 2 && trim($str[1]) == '') {
            $query = $str[0] . 'WHERE 1';
        }else{
            $str = explode('ORDER BY', $str[1]);
            if(count($str) == 2 && trim($str[0]) == ''){
                $query = str_replace('WHERE', '', $query);
            }
        }
    }


    private function get($id): array
    {
        if(gettype($id) == 'string' || gettype($id) == 'integer'){
            print_r($id);
            return $id != ''? $this->q("SELECT * FROM {$this->table} WHERE id='$id';")->fetchAll(PDO::FETCH_ASSOC):[];
        }

        if(gettype($id)== 'array' && !empty($id)){
            $ids = !empty($id['id']) ? $id['id'] : null;
            if($ids){
                return $this->q("SELECT * FROM {$this->table} WHERE id='$ids';")->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $where = Tools::array_implode(", AND ", $id,"[key]='[val]'");
                return $this->q("SELECT * FROM {$this->table} WHERE $where;")->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return [];
    }
}