<?php

namespace Pet\File;

use Pet\File\Exception\FileException;

class FileManager
{
    private static ?self $instance = null;
    private array $disks = [];

    public function __construct()
    {
        $this->disks['local'] = new Storage();
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function disk(?string $name = null): Storage
    {
        $manager = self::instance();

        $name = $name ?? 'local';

        if (!isset($manager->disks[$name])) {
            $manager->disks[$name] = Storage::disk($name);
        }

        return $manager->disks[$name];
    }

    public static function registerDisk(string $name, Storage $storage): void
    {
        self::instance()->disks[$name] = $storage;
    }

    public static function temp(string $content = '', ?string $extension = null): File
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pet';
        $name = bin2hex(random_bytes(8)) . ($extension ? '.' . $extension : '');

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw FileException::directoryNotCreated($dir);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $name;

        if ($content !== '') {
            file_put_contents($path, $content);
        }

        return new File($path);
    }

    public static function ensureDirectory(string $path): string
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw FileException::directoryNotCreated($path);
        }

        return $path;
    }

    public static function cleanDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        return true;
    }

    public static function copyDirectory(string $source, string $destination): bool
    {
        if (!is_dir($source)) {
            throw FileException::notFound($source);
        }

        self::ensureDirectory($destination);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();

            if ($item->isDir()) {
                self::ensureDirectory($destPath);
            } else {
                copy($item->getPathname(), $destPath);
            }
        }

        return true;
    }

    public static function glob(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);

        if ($files === false) {
            return [];
        }

        return array_map(fn(string $path) => new File($path), $files);
    }

    public static function find(string $directory, string $pattern): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && fnmatch($pattern, $fileInfo->getFilename())) {
                $files[] = new File($fileInfo->getPathname());
            }
        }

        return $files;
    }

    public static function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^\w\.\-]/u', '_', $name);
        $name = preg_replace('/_{2,}/', '_', $name);

        return trim($name, '._-');
    }

    public static function uniqueFilename(string $directory, string $name): string
    {
        $path = $directory . DIRECTORY_SEPARATOR . $name;
        $info = pathinfo($path);
        $i = 1;

        while (file_exists($path)) {
            $path = $info['dirname'] . DIRECTORY_SEPARATOR
                  . $info['filename'] . '_' . $i . '.'
                  . ($info['extension'] ?? '');
            $i++;
        }

        return basename($path);
    }

    public static function extension(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/gzip' => 'gz',
            'text/plain' => 'txt',
            'text/html' => 'html',
            'text/css' => 'css',
            'text/javascript' => 'js',
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/csv' => 'csv',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
        ];

        return $map[$mimeType] ?? 'bin';
    }
}
