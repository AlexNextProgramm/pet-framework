<?php

namespace Pet\File;

use Pet\File\Exception\FileException;

/**
 * Объектное представление файла в файловой системе.
 *
 * Предоставляет удобный интерфейс для выполнения операций с отдельным файлом:
 * чтение, запись, копирование, перемещение, удаление, получение метаданных.
 *
 * @package Pet\File
 */
class File
{
    /** @var string Полный путь к файлу */
    private string $path;

    /** @var string|null Кешированный MIME-тип */
    private ?string $mimeType = null;

    /** @var int|null Кешированный размер файла в байтах */
    private ?int $size = null;

    /** @var string|null Кешированный хеш файла */
    private ?string $hash = null;

    /**
     * @param string $path Полный путь к файлу.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Создаёт экземпляр File из массива загруженного файла ($_FILES).
     *
     * @param array $file Элемент из глобального массива $_FILES.
     * @return self
     *
     * @throws FileException Если при загрузке произошла ошибка.
     */
    public static function fromUpload(array $file): self
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw FileException::uploadError(self::uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        return new self($file['tmp_name']);
    }

    /**
     * Возвращает полный путь к файлу.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Проверяет существование файла в файловой системе.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Проверяет, является ли путь обычным файлом.
     *
     * @return bool
     */
    public function isFile(): bool
    {
        return is_file($this->path);
    }

    /**
     * Проверяет, доступен ли файл для чтения.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return is_readable($this->path);
    }

    /**
     * Проверяет, доступен ли файл для записи.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    /**
     * Возвращает имя файла с расширением (basename).
     *
     * @return string
     */
    public function name(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    /**
     * Возвращает имя файла без расширения.
     *
     * @return string
     */
    public function filename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Возвращает расширение файла.
     *
     * @return string
     */
    public function extension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Возвращает путь к директории файла.
     *
     * @return string
     */
    public function dirname(): string
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    /**
     * Возвращает размер файла в байтах.
     *
     * @return int
     *
     * @throws FileException Если файл не существует.
     */
    public function size(): int
    {
        if ($this->size === null) {
            $this->ensureExists();
            $this->size = filesize($this->path);
        }

        return $this->size;
    }

    /**
     * Возвращает размер файла в человекочитаемом формате (B, KB, MB, GB, TB).
     *
     * @return string
     */
    public function sizeFormatted(): string
    {
        $bytes = $this->size();
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Возвращает MIME-тип файла.
     *
     * @return string
     *
     * @throws FileException Если файл не существует.
     */
    public function mimeType(): string
    {
        if ($this->mimeType === null) {
            $this->ensureExists();
            $detected = mime_content_type($this->path);
            $this->mimeType = $detected ?: 'application/octet-stream';
        }

        return $this->mimeType;
    }

    /**
     * Возвращает хеш файла (по умолчанию md5).
     *
     * @param string $algo Алгоритм хеширования (md5, sha1, sha256 и т.д.).
     * @return string
     *
     * @throws FileException Если файл не существует.
     */
    public function hash(string $algo = 'md5'): string
    {
        $key = $algo . '_hash';

        if ($this->hash === null) {
            $this->ensureExists();
            $this->hash = hash_file($algo, $this->path) ?: '';
        }

        return $this->hash;
    }

    /**
     * Читает содержимое файла в строку.
     *
     * @return string
     *
     * @throws FileException Если файл не существует или недоступен для чтения.
     */
    public function content(): string
    {
        $this->ensureExists();
        $this->ensureReadable();

        $content = file_get_contents($this->path);

        if ($content === false) {
            throw FileException::notReadable($this->path);
        }

        return $content;
    }

    /**
     * Записывает данные в файл (перезаписывает содержимое).
     *
     * @param string $content Данные для записи.
     * @return self
     *
     * @throws FileException Если файл недоступен для записи.
     */
    public function put(string $content): self
    {
        $bytes = file_put_contents($this->path, $content);

        if ($bytes === false) {
            throw FileException::notWritable($this->path);
        }

        $this->size = $bytes;
        $this->hash = null;
        $this->mimeType = null;

        return $this;
    }

    /**
     * Дописывает данные в конец файла.
     *
     * @param string $content Данные для добавления.
     * @return self
     *
     * @throws FileException Если файл недоступен для записи.
     */
    public function append(string $content): self
    {
        $bytes = file_put_contents($this->path, $content, FILE_APPEND);

        if ($bytes === false) {
            throw FileException::notWritable($this->path);
        }

        $this->size = null;
        $this->hash = null;

        return $this;
    }

    /**
     * Добавляет данные в начало файла (препендит).
     *
     * @param string $content Данные для добавления.
     * @return self
     */
    public function prepend(string $content): self
    {
        $existing = $this->exists() ? $this->content() : '';

        return $this->put($content . $existing);
    }

    /**
     * Копирует файл в указанное место.
     *
     * @param string $destination Путь назначения.
     * @return self Новый экземпляр File для скопированного файла.
     *
     * @throws FileException Если исходный файл не существует или не удалось скопировать.
     */
    public function copy(string $destination): self
    {
        $this->ensureExists();

        $dir = dirname($destination);
        $this->ensureDir($dir);

        if (!copy($this->path, $destination)) {
            throw new FileException("Не удалось скопировать файл в {$destination}");
        }

        return new self($destination);
    }

    /**
     * Перемещает файл в указанное место.
     *
     * @param string $destination Путь назначения.
     * @return self Тот же экземпляр с обновлённым путём.
     *
     * @throws FileException Если исходный файл не существует или не удалось переместить.
     */
    public function move(string $destination): self
    {
        $this->ensureExists();

        $dir = dirname($destination);
        $this->ensureDir($dir);

        if (!rename($this->path, $destination)) {
            throw new FileException("Не удалось переместить файл в {$destination}");
        }

        $this->path = $destination;
        $this->size = null;
        $this->hash = null;
        $this->mimeType = null;

        return $this;
    }

    /**
     * Переименовывает файл в той же директории.
     *
     * @param string $newName Новое имя файла.
     * @return self
     */
    public function rename(string $newName): self
    {
        $dir = $this->dirname();

        return $this->move($dir . DIRECTORY_SEPARATOR . $newName);
    }

    /**
     * Удаляет файл.
     *
     * @return bool true если файл был удалён, false если файл не существовал.
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        return unlink($this->path);
    }

    /**
     * Проверяет, является ли файл изображением.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimeType(), 'image/');
    }

    /**
     * Проверяет, является ли файл текстовым.
     *
     * @return bool
     */
    public function isText(): bool
    {
        return str_starts_with($this->mimeType(), 'text/');
    }

    /**
     * Проверяет, является ли файл архивом.
     *
     * @return bool
     */
    public function isArchive(): bool
    {
        return in_array($this->mimeType(), [
            'application/zip',
            'application/x-rar-compressed',
            'application/gzip',
            'application/x-7z-compressed',
            'application/x-tar',
        ], true);
    }

    /**
     * Проверяет, является ли файл PDF-документом.
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->mimeType() === 'application/pdf';
    }

    /**
     * Читает файл построчно, пропуская пустые строки.
     *
     * @return string[]
     */
    public function lines(): array
    {
        return file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    /**
     * Возвращает время последней модификации файла (Unix timestamp).
     *
     * @return int
     *
     * @throws FileException Если файл не существует.
     */
    public function lastModified(): int
    {
        $this->ensureExists();

        return filemtime($this->path);
    }

    /**
     * Возвращает права доступа к файлу в восьмеричном формате (например, 0644).
     *
     * @return string
     *
     * @throws FileException Если файл не существует.
     */
    public function permissions(): string
    {
        $this->ensureExists();

        return substr(sprintf('%o', fileperms($this->path)), -4);
    }

    /**
     * Возвращает UID владельца файла.
     *
     * @return int
     *
     * @throws FileException Если файл не существует.
     */
    public function owner(): int
    {
        $this->ensureExists();

        return fileowner($this->path);
    }

    /**
     * Возвращает GID группы файла.
     *
     * @return int
     *
     * @throws FileException Если файл не существует.
     */
    public function group(): int
    {
        $this->ensureExists();

        return filegroup($this->path);
    }

    /**
     * Преобразует информацию о файле в ассоциативный массив.
     *
     * @return array{path: string, name: string, filename: string, extension: string, dirname: string, size: int, size_formatted: string, mime_type: string, is_image: bool, is_text: bool, last_modified: int, permissions: string}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'name' => $this->name(),
            'filename' => $this->filename(),
            'extension' => $this->extension(),
            'dirname' => $this->dirname(),
            'size' => $this->size(),
            'size_formatted' => $this->sizeFormatted(),
            'mime_type' => $this->mimeType(),
            'is_image' => $this->isImage(),
            'is_text' => $this->isText(),
            'last_modified' => $this->lastModified(),
            'permissions' => $this->permissions(),
        ];
    }

    /**
     * Конвертирует изображение текущего файла в WebP.
     *
     * Сохраняет .webp файл рядом с оригиналом.
     *
     * @param  int  $quality Качество WebP (0-100)
     * @return self           Новый экземпляр File для созданного .webp файла
     *
     * @throws FileException Если исходный файл не существует или не удалось конвертировать.
     */
    public function convertImage(int $quality = 100): self
    {
        $this->ensureExists();

        $image = new Image($this->path);
        $image->convertToWebp($quality);

        $dest = $this->dirname() . DIRECTORY_SEPARATOR . $this->filename() . '.webp';

        return new self($dest);
    }

    /**
     * Возвращает путь к файлу при строковом преобразовании.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->path;
    }

    /**
     * Проверяет существование файла и выбрасывает исключение, если его нет.
     *
     * @return void
     *
     * @throws FileException
     */
    private function ensureExists(): void
    {
        if (!$this->exists()) {
            throw FileException::notFound($this->path);
        }
    }

    /**
     * Проверяет доступность файла для чтения.
     *
     * @return void
     *
     * @throws FileException
     */
    private function ensureReadable(): void
    {
        if (!$this->isReadable()) {
            throw FileException::notReadable($this->path);
        }
    }

    /**
     * Создаёт директорию, если она не существует.
     *
     * @param string $dir Путь к директории.
     * @return void
     *
     * @throws FileException Если не удалось создать директорию.
     */
    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw FileException::directoryNotCreated($dir);
        }
    }

    /**
     * Преобразует код ошибки загрузки в человекочитаемое сообщение.
     *
     * @param int $error Код ошибки UPLOAD_ERR_*.
     * @return string
     */
    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'Размер файла превышает максимально допустимый (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'Размер файла превышает максимально допустимый (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная директория для загрузки',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла была остановлена расширением',
            default => 'Неизвестная ошибка загрузки файла',
        };
    }
}
