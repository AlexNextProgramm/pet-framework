<?php

namespace Pet\Errors;

/**
 * Errors — регистрация и логирование фатальных ошибок PHP.
 *
 * При создании экземпляра регистрирует shutdown-обработчик,
 * который при фатальной ошибке записывает информацию в лог-файл.
 */
class Errors
{
    /** @var string Путь к файлу лога */
    private const LOG = ROOT . '/' . LOG;

    /** @var int[] Список типов ошибок, которые попадают в лог */
    private const FATAL_TYPES = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
    ];

    /**
     * Регистрирует shutdown-обработчик для логирования фатальных ошибок.
     */
    public function __construct()
    {
        register_shutdown_function(function (): void {
            $this->log();
        });
    }

    /**
     * Проверяет, была ли фатальная ошибка, и записывает её в лог.
     *
     * @return void
     */
    public function log(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if (!in_array($error['type'], self::FATAL_TYPES, true)) {
            return;
        }

        $this->write(
            sprintf(
                "[%s] %s in %s on line %d",
                $this->errorTypeName($error['type']),
                $error['message'],
                $error['file'],
                $error['line']
            )
        );
    }

    /**
     * Записывает строку в лог-файл.
     *
     * @param string $message Сообщение для записи
     * @return void
     */
    private function write(string $message): void
    {
        $date = date('d.m.Y H:i:s');
        $line = ">>> TIME: {$date} MESS: {$message}\n";
        file_put_contents(self::LOG, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Возвращает человекочитаемое имя типа ошибки.
     *
     * @param int $type Код типа ошибки
     * @return string
     */
    private function errorTypeName(int $type): string
    {
        return match ($type) {
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
            default             => "Unknown Error ({$type})",
        };
    }
}
