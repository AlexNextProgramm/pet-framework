<?php
namespace Pet\Router;

use Exception;
use Pet\Errors\AppException;
use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Tools\Tools;

class Response
{
    const TYPE_JSON = "Content-type: application/json;";
    private static $type = 'Content-type: text/html;';

    public function __construct($status = 200){
        http_response_code($status);
    }
    /**
     * redirect
     * @param  string $path
     * @return void
     */
    public static function redirect(string $path): void
    {

        if ($path == request()->path) {
            Error::setHttp(Error::STATUS_HTTP::FATAL, "redirect to itself is prohibited");
            throw new AppException("redirect to itself is prohibited", E_ERROR);
        } else {
            header("Location: $path");
        }
    }

    public static function code($code) {
        http_response_code($code);
    }

    public static function echo($data)
    {
        if (gettype($data) == 'string') {
            echo $data;
        } else {
            if (self::$type == self::TYPE_JSON) {
                header(self::$type);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public static function die($data)
    {
        if (gettype($data) == 'string') {
            die($data);
        } else {
            if (self::$type == self::TYPE_JSON) {
                header(self::$type);
                die(json_encode($data, JSON_UNESCAPED_UNICODE));
            }
        }
    }
    public static function set($responseHeader): void
    {
        Response::$type = $responseHeader;
    }
}