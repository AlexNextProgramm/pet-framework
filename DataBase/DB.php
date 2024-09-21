<?php

namespace Pet\DataBase;

use PDO;
use Exception;

abstract class DB {

    private  $db_type = DB_TYPE;
    private  $db_host = DB_HOST;
    private  $db_name = DB_NAME;
    private  $db_port = DB_PORT;
    private  $db_user = DB_USER;
    private  $db_password = DB_PASSWORD;
    public $DB = null;

    public function conn(){
        try {
            $this->DB = new PDO("{$this->db_type}:host={$this->db_host}:{$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_password);
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }


    public function q($query) {
    

        return $this->DB->query($query, PDO::FETCH_ASSOC);
    }
}