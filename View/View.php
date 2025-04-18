<?php

namespace Pet\View;

use Pet\Territory;

class View
{
    const DIR_VIEW = PUBLIC_DIR . DS . 'view';
    private $argument;
    /**
     * open
     * @param  string $viewName
     * @param  array $argument
     * @return void
     */
    public function open(string $viewName, array $argument = []): void
    {
        $this->path($viewName);
        if (!is_dir(self::DIR_VIEW) || !file_exists(self::DIR_VIEW . "/$viewName.php")) die("Нет файла или дериктории в view $viewName.php");
        foreach ($argument as $key => $val) {
            ${$key} = $val;
        }
        include_once(self::DIR_VIEW . "/$viewName.php");
    }
    private function path(&$path)
    {
        $path = implode(DS, explode(".", $path));
        return $path;
    }
}
