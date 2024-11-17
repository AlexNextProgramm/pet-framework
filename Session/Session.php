<?php

class Session
{
    public $status;

    public function __construct() {
        $this->status = $this->init();
    }

    private function init(){
        if (session_status() != 2) session_start(['name' => "PET/SESSION"]);
        return session_status();
    }

    static function get(string|null $key = null): string|array
    {
        if (!$key) return $_SESSION;
        return !empty($_SESSION[$key])?$_SESSION[$key]: null; 
    }

    static function set(array $val): void
    {
        foreach($val as $i => $v) $_SESSION[$i] = $v;
    }

    static function kill()
    {
        session_destroy();
    }
}