<?php

namespace Pet\File;

use Pet\File\Exception\FileException;

class Storage
{
    private string $root;
    private string $urlPrefix;

    public function __construct(?string $root = null, ?string $urlPrefix = null)
    {
        $projectRoot = defined('ROOT') ? dirname(ROOT) : getcwd();
        $this->root = $root ?? $projectRoot . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'uploads';
        $this->urlPrefix = $urlPrefix ?? (defined('UPLOADS_URL') ? UPLOADS_URL : '/uploads');
        $this->ensureDir($this->root);
    }

    public static function disk(string $name): self
    {
        $configs = [
            'local' => [
                'root' => defined('ROOT')
                    ? dirname(ROOT) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'uploads'
                    : getcwd() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'uploads',
                'url' => '/uploads',
            ],
            'public' => [
                'root' => defined('ROOT')
                    ? dirname(ROOT) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'uploads'
                    : getcwd() . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'uploads',
                'url' => '/uploads',
            ],
            'tmp' => [
                'root' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pet-uploads',
                'url' => '/tmp-uploads',
            ],
        ];

        $config = $configs[$name] ?? $configs['local'];

        return new self($config['root'], $config['url']);
    }

    public function root(): string
    {
        return $this->root;
    }

    public function urlPrefix(): string
    {
        return $this->urlPrefix;
    }

    public function save(array $file, string $subdir = ''): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw FileException::uploadError();
        }

        $originalName = $file['name'] ?? 'file';
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = bin2hex(random_bytes(8)) . ($ext !== '' ? '.' . $ext : '');

        $relative = $this->joinRelative($subdir, $name);
        $dest = $this->resolve($relative);

        $this->ensureDir(dirname($dest));

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw FileException::saveError('Не удалось сохранить загруженный файл');
        }

        return $relative;
    }

    public function saveWithOriginalName(array $file, string $subdir = ''): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw FileException::uploadError();
        }

        $originalName = $file['name'] ?? 'file';
        $name = $this->sanitizeName($originalName);

        $relative = $this->joinRelative($subdir, $name);
        $dest = $this->resolve($relative);

        $this->ensureDir(dirname($dest));

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw FileException::saveError('Не удалось сохранить загруженный файл');
        }

        return $relative;
    }

    public function saveContent(string $content, string $relativePath): string
    {
        $relativePath = $this->normalizeRelative($relativePath);
        $dest = $this->resolve($relativePath);

        $this->ensureDir(dirname($dest));

        if (file_put_contents($dest, $content) === false) {
            throw FileException::saveError('Не удалось сохранить файл');
        }

        return $relativePath;
    }

    public function saveFile(File $file, string $subdir = ''): string
    {
        $name = $file->name();
        $relative = $this->joinRelative($subdir, $name);
        $dest = $this->resolve($relative);

        $this->ensureDir(dirname($dest));

        $file->copy($dest);

        return $relative;
    }

    public function serve(string $relativePath): void
    {
        try {
            $fullPath = $this->resolve($relativePath);
        } catch (FileException) {
            http_response_code(404);
            return;
        }

        if (!is_file($fullPath)) {
            http_response_code(404);
            return;
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=31536000');

        readfile($fullPath);
        exit;
    }

    public function download(string $relativePath, ?string $displayName = null): void
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_file($fullPath)) {
            throw FileException::notFound($relativePath);
        }

        $displayName = $displayName ?? basename($relativePath);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $displayName . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: no-cache');

        readfile($fullPath);
        exit;
    }

    public function url(string $relativePath): string
    {
        $relativePath = ltrim($this->normalizeRelative($relativePath), '/');

        return rtrim($this->urlPrefix, '/') . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    }

    public function path(string $relativePath): string
    {
        return $this->resolve($relativePath);
    }

    public function exists(string $relativePath): bool
    {
        return is_file($this->resolve($relativePath));
    }

    public function delete(string $relativePath): bool
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_file($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }

    public function deleteDirectory(string $relativePath): bool
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_dir($fullPath)) {
            return false;
        }

        $this->removeDirectory($fullPath);

        return true;
    }

    public function files(?string $subdir = null): array
    {
        $dir = $subdir !== null ? $this->resolve($subdir) : $this->root;

        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        $iterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getFilename();
            }
        }

        sort($files);

        return $files;
    }

    public function allFiles(?string $subdir = null): array
    {
        $dir = $subdir !== null ? $this->resolve($subdir) : $this->root;

        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    public function directories(?string $subdir = null): array
    {
        $dir = $subdir !== null ? $this->resolve($subdir) : $this->root;

        if (!is_dir($dir)) {
            return [];
        }

        $dirs = [];
        $iterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                $dirs[] = $fileInfo->getFilename();
            }
        }

        sort($dirs);

        return $dirs;
    }

    public function makeDirectory(string $relativePath): bool
    {
        $fullPath = $this->resolve($relativePath);

        if (is_dir($fullPath)) {
            return true;
        }

        return mkdir($fullPath, 0755, true);
    }

    public function copy(string $from, string $to): string
    {
        $source = $this->resolve($from);
        $dest = $this->resolve($to);

        if (!is_file($source)) {
            throw FileException::notFound($from);
        }

        $this->ensureDir(dirname($dest));

        if (!copy($source, $dest)) {
            throw new FileException("Не удалось скопировать {$from} в {$to}");
        }

        return $to;
    }

    public function move(string $from, string $to): string
    {
        $source = $this->resolve($from);
        $dest = $this->resolve($to);

        if (!is_file($source)) {
            throw FileException::notFound($from);
        }

        $this->ensureDir(dirname($dest));

        if (!rename($source, $dest)) {
            throw new FileException("Не удалось переместить {$from} в {$to}");
        }

        return $to;
    }

    public function size(string $relativePath): int
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_file($fullPath)) {
            throw FileException::notFound($relativePath);
        }

        return filesize($fullPath);
    }

    public function lastModified(string $relativePath): int
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_file($fullPath)) {
            throw FileException::notFound($relativePath);
        }

        return filemtime($fullPath);
    }

    public function mimeType(string $relativePath): string
    {
        $fullPath = $this->resolve($relativePath);

        if (!is_file($fullPath)) {
            throw FileException::notFound($relativePath);
        }

        return mime_content_type($fullPath) ?: 'application/octet-stream';
    }

    public function file(string $relativePath): File
    {
        return new File($this->resolve($relativePath));
    }

    public function __invoke($request): void
    {
        $this->serve(supple('*') ?? '');
    }

    private function resolve(string $relativePath): string
    {
        $relativePath = $this->normalizeRelative($relativePath);

        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function normalizeRelative(string $relativePath): string
    {
        $relativePath = str_replace(['\\', "\0"], '', $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            throw FileException::invalidPath($relativePath);
        }

        return $relativePath;
    }

    private function joinRelative(string $subdir, string $name): string
    {
        $subdir = trim(str_replace('\\', '/', $subdir), '/');

        return $subdir !== '' ? $subdir . '/' . $name : $name;
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw FileException::directoryNotCreated($dir);
        }
    }

    private function sanitizeName(string $name): string
    {
        $name = preg_replace('/[^\w\.\-]/u', '_', $name);
        $name = preg_replace('/_{2,}/', '_', $name);

        return trim($name, '._-');
    }

    private function removeDirectory(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
