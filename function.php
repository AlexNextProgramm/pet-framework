<?php


function search_include_class($path, $class = '') {

    foreach (scandir($path) as $dir) {

        if ($dir == ".." || $dir == '.') continue;
        $dir = $path . "/" . $dir;
        if (is_dir($dir)) search_include_class($dir, $class);

        if (is_readable($dir)) {

            $file = file($dir);

            foreach ($file as $row)
                if (str_contains($row, "class " . $class)) require_once($dir);
        }
    }
}

function env($constans = null, $default = null) : ?string
{
    if(!$constans) return null;
    if (!file_exists(ROOT_DIR . '/.env')) echo 'Нет файла .env в корне проекта';

    $env = file(ROOT_DIR . '/.env');

    foreach ($env as $str) {
        if (str_contains(trim($str), '#') && strpos(trim($str), "#") == 0) continue;
        if (str_contains($str, '=')) {

            $param = explode('=', $str);

            if (trim($param[0]) == trim($constans)) {
               $param = trim(str_replace([';', '"', "'",], '', $param[1]));
               return $param == ''? $default: $param;
            }
        }
    }
    return $default;
}


function setConstantEnv($ROOT_DIR){
    if (!file_exists($ROOT_DIR . '/.env')) echo 'Нет файла .env в корне проекта';
    $env = file($ROOT_DIR . '/.env');
    foreach ($env as $str) {
        if (str_contains(trim($str), '#') && strpos(trim($str), "#") == 0) continue;
        if (str_contains($str, '=')) {
            $param = explode('=', $str);
            $value= trim(str_replace([';', '"', "'",], '', $param[1]));

            if(!defined(trim($param[0]))){
                define(trim($param[0]), ($value == '' ? null :  $value));
            }
        }
    }
}

function c($name)
{

}
