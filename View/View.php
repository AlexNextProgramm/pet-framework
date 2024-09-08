<?php
namespace Pet\View;

class View{
    const DIR_VIEW = PUBLIC_DIR.'/view';

    function open($name){
        if(!is_dir(self::DIR_VIEW) && !file_exists(self::DIR_VIEW."/$name.php")) die("Нет файла или дериктории в view");
        $hello = 'hello World';
        include_once self::DIR_VIEW."/$name.php";
    }
}
?>