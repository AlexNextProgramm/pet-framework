<?php
function search_include_class($path, $class = '')
{

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

function env($constans = null, $default = null){
  
    if(!file_exists(PUBLIC_DIR.'/../.env')) echo 'Нет файла env в корне проекта';

    $env = file(PUBLIC_DIR.'/.env');
 
     foreach($env as $str){
         if (str_contains(trim($str), '#') && strpos(trim($str),"#") == 0) continue;
         if(str_contains($str, '=')){
 
             $param = explode('=', $str);
 
             if(trim($param[0]) == trim($constans)){
                 return trim(str_replace([';','"',"'",],'', $param[1] ));
             }
         }
 
     }
     return $default;
 }

?>