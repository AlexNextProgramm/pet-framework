<?php
namespace Pet\Tools;

use Exception;

class Tools{


    static function jsonDe(string $value, bool $bool = true):array|object
    {
        try{
            return json_decode($value, $bool);
        }catch(Exception $e){
            return [];
        }
    }
}
?>