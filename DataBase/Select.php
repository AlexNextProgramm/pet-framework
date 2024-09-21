<?php
namespace Pet\DataBase;
use Pet\DataBase\DB;

class Select extends DB{


    public function __construct()
    {
        $this->conn();
    }

    public function factory($query)
    {
        return $this->q($query);
    }
    

}
?>