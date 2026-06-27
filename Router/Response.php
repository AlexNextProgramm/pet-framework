<?php
namespace Pet\Router;

use Exception;
use Pet\Errors\AppException;
use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Tools\Tools;

class Response
{
    private static string $type = Header::HTML;

    public function __construct(int $status = 200){
        Header::status($status);
    }

    /**
     * redirect
     * @param  string $path
     * @return void
     */
    public static function redirect(string $path): void
    {
        if ($path == request()->path) {
            Error::setHttp(HTTP::FATAL, "redirect to itself is prohibited");
            throw new AppException("redirect to itself is prohibited", E_ERROR);
        } else {
            if (!Header::sent()) {
                ob_clean();
                Header::location($path);
            }
        }
    }

    public static function code(int $code): void {
        Header::status($code);
    }

    public static function echo(mixed $data): void
    {
        if (gettype($data) == 'string') {
            echo $data;
        } else {
            if (self::$type === Header::JSON && !Header::sent()) {
                Header::json();
            }
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    public static function die(mixed $data): never
    {
        ob_clean();
        if (gettype($data) == 'string') {
            die($data);
        } else {
            if (self::$type === Header::JSON && !Header::sent()) {
                Header::json();
            }
            die(json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }

    public static function set(string $responseHeader): void
    {
        self::$type = $responseHeader;
    }
}