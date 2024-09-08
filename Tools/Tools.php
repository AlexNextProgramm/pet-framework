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


    static function strRep($i, $seporator, &$string, $if = null): string
    {
        $string = str_split($string);
        if ($if && $string[$i] === $if) $string[$i] = $seporator;
    
        if (!$if) $string[$i] = $seporator;
        return implode("", $string);
    }
}
?>