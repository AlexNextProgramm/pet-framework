<?php

namespace Pet\View;

use Pet\Errors\AppException;

class View
{
    const DIR_VIEW = VIEW_DIR;
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
        if (!file_exists(self::DIR_VIEW . DS . $viewName)) {
            throw new AppException("Not file in class view", E_ERROR);
        }
        self::$argument = array_merge(self::$argument, $argument);
        foreach (self::$argument as $key => $val) {
            if (isset(${$key})) {
                throw new AppException("You are trying to redefine a variable $key");
            }
            ${$key} = $val;
        }
        include self::DIR_VIEW . DS . "$viewName";
    }

    public static function append(array $data){
        self::$argument = array_merge(self::$argument, $data);
    }

    /**
     * getPath
     *
     * @param  mixed $path
     * @return void
     */
    public static function gp(string $path, string $exp = ".php"): string
    {
        return str_replace(".", DS, $path)."$exp";
    }

    public static function getTemplate($filename, $params = [])
    {       $templatePath = self::DIR_VIEW . DS . self::gp($filename);
        if (is_file($templatePath)) {
            ob_start();
            if (!empty($params)) {
                extract($params, EXTR_SKIP | EXTR_REFS);
            }

            include $templatePath;
            return ob_get_clean();
        }
        return false;
    }
}