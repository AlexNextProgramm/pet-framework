<?php

namespace Pet\Router;

class HTTP {
    // 1xx Informational
    public const CONTINUE = 100;
    public const SWITCHING_PROTOCOLS = 101;

    // 2xx Success
    public const SUCCESS = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NO_CONTENT = 204;

    // 3xx Redirection
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const NOT_MODIFIED = 304;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;

    // 4xx Client Errors
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const CONFLICT = 409;
    public const GONE = 410;
    public const UNPROCESSABLE_ENTITY = 422;
    public const TOO_MANY_REQUESTS = 429;

    // 5xx Server Errors
    public const FATAL = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;

    /**
     * Возвращает текстовое описание HTTP-статуса по его коду.
     *
     * @param int $code HTTP-статус код
     * @return string|null Текстовое описание или null, если код не найден
     */
    public static function text(int $code): ?string
    {
        $messages = [
            self::CONTINUE => 'Continue',
            self::SWITCHING_PROTOCOLS => 'Switching Protocols',

            self::SUCCESS => 'OK',
            self::CREATED => 'Created',
            self::ACCEPTED => 'Accepted',
            self::NO_CONTENT => 'No Content',

            self::MOVED_PERMANENTLY => 'Moved Permanently',
            self::FOUND => 'Found',
            self::SEE_OTHER => 'See Other',
            self::NOT_MODIFIED => 'Not Modified',
            self::TEMPORARY_REDIRECT => 'Temporary Redirect',
            self::PERMANENT_REDIRECT => 'Permanent Redirect',

            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::CONFLICT => 'Conflict',
            self::GONE => 'Gone',
            self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
            self::TOO_MANY_REQUESTS => 'Too Many Requests',

            self::FATAL => 'Internal Server Error',
            self::NOT_IMPLEMENTED => 'Not Implemented',
            self::BAD_GATEWAY => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE => 'Service Unavailable',
            self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        ];

        return $messages[$code] ?? null;
    }
}