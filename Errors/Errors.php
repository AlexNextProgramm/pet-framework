<?php

namespace Pet\Errors;

class Errors
{
    const LOG = ROOT . '/' . LOG;

    public function __construct()
    {
        $this->register_error();
    }

    public function register_error()
    {
        register_shutdown_function(function () {
            $this->log();
        });
    }

    public function log()
    {
        if (($error = error_get_last())) {
            $this->set($error['message'] . ' FILE ' . $error['file'] . ' Line: ' . $error['line']);
        }
    }

    private function set($str)
    {
        $date = date('d.m.Y H:i:s');
        $LOG = ">>> TIME: {$date} MESS: {$str}  \n\r";
        file_put_contents(self::LOG, $LOG, FILE_APPEND | LOCK_EX);
    }
}
