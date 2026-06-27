<?php

namespace Pet\File;

class MimeTypeDetector
{
    private static array $extensionMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'gz' => 'application/gzip',
        'tar' => 'application/x-tar',
        '7z' => 'application/x-7z-compressed',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'csv' => 'text/csv',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'webm' => 'video/webm',
        'mkv' => 'video/x-matroska',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',
        'php' => 'text/plain',
        'py' => 'text/plain',
        'rb' => 'text/plain',
        'go' => 'text/plain',
        'rs' => 'text/plain',
        'ts' => 'text/plain',
        'vue' => 'text/plain',
        'md' => 'text/markdown',
        'yaml' => 'application/x-yaml',
        'yml' => 'application/x-yaml',
        'env' => 'text/plain',
        'log' => 'text/plain',
        'sql' => 'text/plain',
        'sh' => 'application/x-sh',
        'bat' => 'application/x-msdos-program',
    ];

    private static array $mimeExtensionMap = [];

    private static function buildReverseMap(): void
    {
        if (self::$mimeExtensionMap !== []) {
            return;
        }

        foreach (self::$extensionMap as $ext => $mime) {
            if (!isset(self::$mimeExtensionMap[$mime])) {
                self::$mimeExtensionMap[$mime] = $ext;
            }
        }
    }

    public static function fromExtension(string $extension): string
    {
        $extension = strtolower(trim($extension, '. '));

        return self::$extensionMap[$extension] ?? 'application/octet-stream';
    }

    public static function fromFile(string $path): string
    {
        if (!is_file($path)) {
            return 'application/octet-stream';
        }

        $detected = mime_content_type($path);

        return $detected ?: 'application/octet-stream';
    }

    public static function extensionFor(string $mimeType): string
    {
        self::buildReverseMap();

        $mimeType = strtolower($mimeType);

        return self::$mimeExtensionMap[$mimeType] ?? 'bin';
    }

    public static function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public static function isVideo(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'video/');
    }

    public static function isAudio(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'audio/');
    }

    public static function isText(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'text/');
    }

    public static function isArchive(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/zip',
            'application/x-rar-compressed',
            'application/gzip',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/x-bzip',
            'application/x-bzip2',
        ], true);
    }

    public static function isDocument(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/csv',
        ], true);
    }

    public static function isExecutable(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/x-sh',
            'application/x-msdos-program',
            'application/x-executable',
            'application/x-elf',
        ], true);
    }

    public static function isWebSafeImage(string $mimeType): bool
    {
        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true);
    }

    public static function isAllowed(array $allowedTypes, string $mimeType): bool
    {
        foreach ($allowedTypes as $allowed) {
            if (str_contains($allowed, '*')) {
                $prefix = rtrim($allowed, '/*');

                if (str_starts_with($mimeType, $prefix)) {
                    return true;
                }
            } elseif ($allowed === $mimeType) {
                return true;
            }
        }

        return false;
    }

    public static function iconFor(string $mimeType): string
    {
        if (self::isImage($mimeType)) return '🖼️';
        if (self::isVideo($mimeType)) return '🎬';
        if (self::isAudio($mimeType)) return '🎵';
        if (self::isArchive($mimeType)) return '📦';
        if (self::isDocument($mimeType)) return '📄';
        if (self::isText($mimeType)) return '📝';

        return '📎';
    }

    public static function category(string $mimeType): string
    {
        if (self::isImage($mimeType)) return 'image';
        if (self::isVideo($mimeType)) return 'video';
        if (self::isAudio($mimeType)) return 'audio';
        if (self::isArchive($mimeType)) return 'archive';
        if (self::isDocument($mimeType)) return 'document';
        if (self::isText($mimeType)) return 'text';

        return 'other';
    }

    public static function allExtensions(): array
    {
        return array_keys(self::$extensionMap);
    }

    public static function allMimeTypes(): array
    {
        return array_unique(array_values(self::$extensionMap));
    }

    public static function register(string $extension, string $mimeType): void
    {
        self::$extensionMap[strtolower(trim($extension, '. '))] = $mimeType;
        self::$mimeExtensionMap = [];
    }
}
