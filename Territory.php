<?php
namespace Pet;

class Territory {

    static function getClass($history = 2) {

        $array = debug_backtrace();

        if (!empty($array[$history]) && !empty($array[$history]['object'])) {
            return $array[$history]['object'];
        }
        return false;
    }
}