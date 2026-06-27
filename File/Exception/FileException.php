<?php

namespace Pet\File\Exception;

use RuntimeException;

/**
 * Исключение для ошибок, связанных с файловыми операциями.
 *
 * Содержит набор фабричных методов для создания типизированных исключений
 * с предопределёнными HTTP-статусами и русскоязычными сообщениями.
 *
 * @package Pet\File\Exception
 */
class FileException extends RuntimeException
{
    /**
     * Файл не найден по указанному пути.
     *
     * @param string $path Путь к файлу.
     * @return self
     */
    public static function notFound(string $path): self
    {
        return new self("Файл не найден: {$path}", 404);
    }

    /**
     * Файл недоступен для чтения.
     *
     * @param string $path Путь к файлу.
     * @return self
     */
    public static function notReadable(string $path): self
    {
        return new self("Файл недоступен для чтения: {$path}", 403);
    }

    /**
     * Файл недоступен для записи.
     *
     * @param string $path Путь к файлу.
     * @return self
     */
    public static function notWritable(string $path): self
    {
        return new self("Файл недоступен для записи: {$path}", 403);
    }

    /**
     * Ошибка при загрузке файла через HTTP.
     *
     * @param string $message Текст ошибки.
     * @return self
     */
    public static function uploadError(string $message = 'Ошибка загрузки файла'): self
    {
        return new self($message, 400);
    }

    /**
     * Некорректный путь к файлу (содержит ".." или пустой).
     *
     * @param string $path Путь к файлу.
     * @return self
     */
    public static function invalidPath(string $path): self
    {
        return new self("Некорректный путь к файлу: {$path}", 400);
    }

    /**
     * Некорректный файл изображения.
     *
     * @param string $message Текст ошибки.
     * @return self
     */
    public static function invalidImage(string $message = 'Некорректное изображение'): self
    {
        return new self($message, 400);
    }

    /**
     * Не удалось создать директорию.
     *
     * @param string $dir Путь к директории.
     * @return self
     */
    public static function directoryNotCreated(string $dir): self
    {
        return new self("Не удалось создать каталог: {$dir}", 500);
    }

    /**
     * Ошибка сохранения файла.
     *
     * @param string $message Текст ошибки.
     * @return self
     */
    public static function saveError(string $message = 'Не удалось сохранить файл'): self
    {
        return new self($message, 500);
    }
}
