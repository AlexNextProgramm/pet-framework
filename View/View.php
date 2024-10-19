<?php

namespace Pet\View;

class View
{
    const DIR_VIEW = PUBLIC_DIR . '/view';

    function open($name, $argument = [])
    {
        if (!is_dir(self::DIR_VIEW) || !file_exists(self::DIR_VIEW . "/$name.php")) die("Нет файла или дериктории в view $name.php");
        foreach ($argument as $key => $val) ${$key} = $val;
        include_once self::DIR_VIEW . "/$name.php";
    }
}
