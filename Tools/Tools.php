<?php

namespace Pet\Tools;

use Exception;

class Tools {


    /**
     * jsonDe
     *
     * @param  mixed $value
     * @param  mixed $bool
     * @return array
     */
    static function jsonDe(string $value, bool $bool = true): array|object {
        try {
            return json_decode($value, $bool);
        } catch (Exception $e) {
            return [];
        }
    }


    /**
     * strRep
     *
     * @param  mixed $i
     * @param  mixed $seporator
     * @param  mixed $string
     * @param  mixed $if
     * @return string
     */
    static function strRep($i, $seporator, &$string, $if = null): string {
        $string = str_split($string);
        if ($if && $string[$i] === $if) $string[$i] = $seporator;

        if (!$if) $string[$i] = $seporator;
        return implode("", $string);
    }

    /**
     * array_implode
     *
     * @param  mixed $seporator
     * @param  mixed $arrKeyValue
     * @param  mixed $between [key] [val]
     * @return string
     * 
     */
    static function array_implode(string $seporator,  array $arrKeyValue, string $between = '[key]=[val]', $callback = null): string {

        return implode($seporator, array_map(
            fn($v, $k)=> $callback?$callback($v, $k, $between):str_replace(['[val]', '[key]'],[$v, $k], $between),
            $arrKeyValue,
            array_keys($arrKeyValue)
        ));
    }
    
    /**
     * is_assos
     *
     * @param  array $array
     * @return string "index"|"gibrid"|"assos"
     */
    static function is_assos(array $array):string
     {
        $keys = array_keys($array);
        $str = implode("", $keys);
        if(is_numeric($str)) return 'index';
        try{
            if (array_sum($keys) > 0 || array_sum($keys) == 0 && key_exists('0', $array)) {
                return "gibrid";
            } else {
                return 'assos';
            }
        }catch(Exception $e){
            return 'assos';
        }
    }
}
