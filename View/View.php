<?php

namespace Pet\View;

class View
{
    const DIR_VIEW = PUBLIC_DIR . '/view';

    /**
     * open
     *
     * @param  string $viewName
     * @param  array $argument
     * @return void
     */
    function open(string $viewName, array $argument = []): void
    {
        if (!is_dir(self::DIR_VIEW) || !file_exists(self::DIR_VIEW . "/$viewName.php")) die("Нет файла или дериктории в view $viewName.php");
        foreach ($argument as $key => $val) ${$key} = $val;
        include_once self::DIR_VIEW . "/$viewName.php";
    }
}
