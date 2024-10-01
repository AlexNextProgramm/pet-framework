<?php
namespace Pet\Model;

use Pet\DataBase\Delete;
use Pet\DataBase\Update;

abstract class Model extends Update{



    function find($searh = [], $column  = []):array
    {
       return  $this->select($column)->And($searh)->fetch();
    }
}