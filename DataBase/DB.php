<?php
namespace Pet\DataBase;


abstract class DB{
    private  $BASE = DB_TYPE;
    private  $HOST = DB_HOST;
    private  $DB = DB_NAME;
    private  $USER = DB_USER;
    private  $PASSWORD = DB_PASSWORD;

    public function conn(){

    }

    public function q(){

    }

}
?>