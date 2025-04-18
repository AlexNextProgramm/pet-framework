<?php

namespace Pet\Errors;

use Exception;

class AppException extends Exception
{
    // Дополнительные свойства
    protected $errorCode;

    // Конструктор
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $code; // Сохранение кода ошибки
    }

    // Метод для получения кода ошибки
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    // Метод для логирования ошибок (пример)
    public function logError()
    {
        // Логирование ошибки в файл или систему логирования
        file_put_contents('app_error_log.txt', date('Y-m-d H:i:s') . " - " . $this->getMessage() . PHP_EOL, FILE_APPEND);
    }
}