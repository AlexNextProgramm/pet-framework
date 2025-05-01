<?php

namespace Pet\View;

use Pet\Errors\AppException;

class View
{
    const DIR_VIEW = PUBLIC_DIR . DS . 'view';
    private static $argument = [];
    /**
     * open
     * @param  string $viewName
     * @param  array $argument
     * @return void
     */
    public static function open(string $viewName, array $argument = []): void {
        $viewName = implode(DS, explode(".", $viewName)) . ".php";
        if (!is_dir(self::DIR_VIEW)) {
            throw new AppException("not directory view", E_ERROR);
        }
        if (!file_exists(self::DIR_VIEW . DS . $viewName . p)) {
            throw new AppException("Not file in class view", E_ERROR);
        }
        self::$argument += $argument;
        foreach (self::$argument as $key => $val) {
            ${$key} = $val;
        }
        include self::DIR_VIEW . DS . "$viewName";
    }

    public static function append(array $data){
        self::$argument += $data;
    }
}