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
}