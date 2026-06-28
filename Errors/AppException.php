<?php

namespace Pet\Errors;

use Exception;
use Throwable;

/**
 * AppException — базовое исключение фреймворка PET.
 *
 * Используется во всех компонентах для единообразной обработки ошибок.
 * Наследует родительский {@see Exception}, код ошибки хранится в родительском свойстве `code`.
 */
class AppException extends Exception
{
    /**
     * @param string         $message  Сообщение об ошибке
     * @param int            $code     Код ошибки (по умолчанию 0)
     * @param Throwable|null $previous Предыдущее исключение
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}