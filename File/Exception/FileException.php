<?php

namespace Pet\File\Exception;

use RuntimeException;

class FileException extends RuntimeException
{
    public static function notFound(string $path): self
    {
        return new self("Файл не найден: {$path}", 404);
    }

    public static function notReadable(string $path): self
    {
        return new self("Файл недоступен для чтения: {$path}", 403);
    }

    public static function notWritable(string $path): self
    {
        return new self("Файл недоступен для записи: {$path}", 403);
    }

    public static function uploadError(string $message = 'Ошибка загрузки файла'): self
    {
        return new self($message, 400);
    }

    public static function invalidPath(string $path): self
    {
        return new self("Некорректный путь к файлу: {$path}", 400);
    }

    public static function invalidImage(string $message = 'Некорректное изображение'): self
    {
        return new self($message, 400);
    }

    public static function directoryNotCreated(string $dir): self
    {
        return new self("Не удалось создать каталог: {$dir}", 500);
    }

    public static function saveError(string $message = 'Не удалось сохранить файл'): self
    {
        return new self($message, 500);
    }
}
