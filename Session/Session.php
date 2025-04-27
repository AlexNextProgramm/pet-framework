<?php
namespace Pet\Session;

class Session
{
    public $status;
    public static $name = 'PET/SESSION' ;
    public function __construct()
    {
        $this->status = $this->init();
    }

    private function init() {
        $status = session_status();
        if ( $status == PHP_SESSION_NONE && $status != PHP_SESSION_DISABLED){
            session_start(['name' => self::$name]);
        }
        return session_status();
    }

    public static function get(string|null $key = null): string|array|null
    {
        if (!$key) return $_SESSION;
        return !empty($_SESSION[$key])?$_SESSION[$key]: null;
    }

    public static function set(array|object $val): void
    {
        foreach ($val as $i => $v) $_SESSION[$i] = $v;
    }

    public static function kill()
    {
        session_destroy();
    }
}