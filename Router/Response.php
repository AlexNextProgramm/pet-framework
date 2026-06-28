<?php
namespace Pet\Router;

use Exception;
use Pet\Errors\AppException;
use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Tools\Tools;

class Response
{

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
        !Header::sent()? Header::type(Header::HTML) : '';
        echo $data;
    }

    public static function json(array|object $data, $code = HTTP::SUCCESS):void
    {
        Header::status($code);
        !Header::sent() ? Header::json() : '';
        die(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public static function html(string $data, $code = HTTP::SUCCESS) {
        Header::status($code);
        !Header::sent() ? Header::html() : '';
        die($data);
    }

    public static function svg(string $data, $code = HTTP::SUCCESS) {
        Header::type(Header::SVG);
        die($data);
    }
}