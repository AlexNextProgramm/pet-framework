<?php
namespace Pet\Git;

class Git{
    static $code;

    static function version():string|false
    {
        return self::exec('git -v');
    }

    static function exec($command): string|false
    {
        return system($command, self::$code);
    }
    static function status(): array
    {
        $str = self::exec('git status -s');
        $result = [];
        foreach(explode("\n", $str) as $row){
            $row = explode(' ', $row);
            $result[] = [
                'action' => $row[0],
                'file' => basename($row[1]),
                'path' => dirname($row[1])
            ];
        }
        return  $result;
    }

    static function add($file = ".") {
        self::exec("git add $file");
    }
}