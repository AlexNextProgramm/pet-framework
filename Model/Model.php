<?php
namespace Pet\Model;

use Pet\DataBase\DB;
use Pet\DataBase\Delete;
use Pet\DataBase\Update;
use Pet\DataBase\Select;
use Pet\DataBase\Insert;

abstract class Model extends DB{
    use  Select, Update, Delete, Insert;
    
    /**
     * find
     *
     * @param  array $searh
     * @param  array $column
     * @param  int $limit
     * @return array
     */
    function find($searh = [], $column  = [], $limit = null):array
    {
        if($limit){
            return  $this->select($column)->And($searh)->limit($limit)->fetch();
        }else{
            return  $this->select($column)->And($searh)->fetch();
        }
    }
}   