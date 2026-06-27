<?php

namespace Pet\File;

use Pet\File\Exception\FileException;

class File
{
    private string $path;
    private ?string $mimeType = null;
    private ?int $size = null;
    private ?string $hash = null;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function fromUpload(array $file): self
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw FileException::uploadError(self::uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        return new self($file['tmp_name']);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function isFile(): bool
    {
        return is_file($this->path);
    }

    public function isReadable(): bool
    {
        return is_readable($this->path);
    }

    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    public function name(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    public function filename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    public function extension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function dirname(): string
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    public function size(): int
    {
        if ($this->size === null) {
            $this->ensureExists();
            $this->size = filesize($this->path);
        }

        return $this->size;
    }

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

    public function mimeType(): string
    {
        if ($this->mimeType === null) {
            $this->ensureExists();
            $detected = mime_content_type($this->path);
            $this->mimeType = $detected ?: 'application/octet-stream';
        }

        return $this->mimeType;
    }

    public function hash(string $algo = 'md5'): string
    {
        $key = $algo . '_hash';

        if ($this->hash === null) {
            $this->ensureExists();
            $this->hash = hash_file($algo, $this->path) ?: '';
        }

        return $this->hash;
    }

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

    public function prepend(string $content): self
    {
        $existing = $this->exists() ? $this->content() : '';

        return $this->put($content . $existing);
    }

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

    public function rename(string $newName): self
    {
        $dir = $this->dirname();

        return $this->move($dir . DIRECTORY_SEPARATOR . $newName);
    }

    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        return unlink($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType(), 'image/');
    }

    public function isText(): bool
    {
        return str_starts_with($this->mimeType(), 'text/');
    }

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

    public function isPdf(): bool
    {
        return $this->mimeType() === 'application/pdf';
    }

    public function lines(): array
    {
        return file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    public function lastModified(): int
    {
        $this->ensureExists();

        return filemtime($this->path);
    }

    public function permissions(): string
    {
        $this->ensureExists();

        return substr(sprintf('%o', fileperms($this->path)), -4);
    }

    public function owner(): int
    {
        $this->ensureExists();

        return fileowner($this->path);
    }

    public function group(): int
    {
        $this->ensureExists();

        return filegroup($this->path);
    }

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

    public function __toString(): string
    {
        return $this->path;
    }

    private function ensureExists(): void
    {
        if (!$this->exists()) {
            throw FileException::notFound($this->path);
        }
    }

    private function ensureReadable(): void
    {
        if (!$this->isReadable()) {
            throw FileException::notReadable($this->path);
        }
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw FileException::directoryNotCreated($dir);
        }
    }

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
