<?php

namespace Pet\File;

use Pet\File\Exception\FileException;

class Image
{
    private string $path;
    private ?int $width = null;
    private ?int $height = null;
    private ?string $mimeType = null;
    private ?\GdImage $resource = null;

    public function __construct(string $path)
    {
        if (!extension_loaded('gd')) {
            throw new FileException('PHP-расширение GD не установлено');
        }

        if (!is_file($path)) {
            throw FileException::notFound($path);
        }

        $this->path = $path;
    }

    public static function fromFile(File $file): self
    {
        return new self($file->path());
    }

    public static function fromString(string $data): self
    {
        if (!extension_loaded('gd')) {
            throw new FileException('PHP-расширение GD не установлено');
        }

        $resource = @imagecreatefromstring($data);

        if ($resource === false) {
            throw FileException::invalidImage('Не удалось создать изображение из строки');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'img_') . '.png';
        imagepng($resource, $tmpPath);
        imagedestroy($resource);

        $instance = new self($tmpPath);
        $instance->load();

        return $instance;
    }

    public static function create(int $width, int $height, string $color = '#ffffff'): self
    {
        if (!extension_loaded('gd')) {
            throw new FileException('PHP-расширение GD не установлено');
        }

        $resource = imagecreatetruecolor($width, $height);

        if ($resource === false) {
            throw FileException::invalidImage('Не удалось создать изображение');
        }

        $rgb = self::hexToRgb($color);
        $colorAlloc = imagecolorallocate($resource, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($resource, 0, 0, $colorAlloc);

        $tmpPath = tempnam(sys_get_temp_dir(), 'img_') . '.png';
        imagepng($resource, $tmpPath);
        imagedestroy($resource);

        return new self($tmpPath);
    }

    public function width(): int
    {
        $this->load();

        return $this->width;
    }

    public function height(): int
    {
        $this->load();

        return $this->height;
    }

    public function mimeType(): string
    {
        $this->load();

        return $this->mimeType;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function resize(int $width, ?int $height = null, bool $keepRatio = true): self
    {
        $this->load();

        if ($keepRatio) {
            $ratio = $this->width / $this->height;

            if ($height === null) {
                $height = (int)round($width / $ratio);
            } elseif ($width === null) {
                $width = (int)round($height * $ratio);
            } else {
                $newRatio = $width / $height;

                if ($newRatio > $ratio) {
                    $width = (int)round($height * $ratio);
                } else {
                    $height = (int)round($width / $ratio);
                }
            }
        }

        $resized = imagescale($this->resource, $width, $height, IMG_BILINEAR_FIXED);

        if ($resized === false) {
            throw FileException::invalidImage('Не удалось изменить размер изображения');
        }

        imagedestroy($this->resource);
        $this->resource = $resized;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function resizeToWidth(int $width): self
    {
        return $this->resize($width, null, true);
    }

    public function resizeToHeight(int $height): self
    {
        return $this->resize(null, $height, true);
    }

    public function crop(int $x, int $y, int $width, int $height): self
    {
        $this->load();

        $cropped = imagecrop($this->resource, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        if ($cropped === false) {
            throw FileException::invalidImage('Не удалось обрезать изображение');
        }

        imagedestroy($this->resource);
        $this->resource = $cropped;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function cropCenter(int $width, int $height): self
    {
        $this->load();

        $x = max(0, (int)(($this->width - $width) / 2));
        $y = max(0, (int)(($this->height - $height) / 2));

        return $this->crop($x, $y, min($width, $this->width), min($height, $this->height));
    }

    public function cropThumbnail(int $size): self
    {
        $this->load();

        $shortSide = min($this->width, $this->height);
        $x = (int)(($this->width - $shortSide) / 2);
        $y = (int)(($this->height - $shortSide) / 2);

        $this->crop($x, $y, $shortSide, $shortSide);

        return $this->resize($size, $size, false);
    }

    public function rotate(float $angle, string $bgColor = '#000000'): self
    {
        $this->load();

        $rgb = self::hexToRgb($bgColor);
        $bgIndex = imagecolorallocate($this->resource, $rgb[0], $rgb[1], $rgb[2]);

        $rotated = imagerotate($this->resource, $angle, $bgIndex);

        if ($rotated === false) {
            throw FileException::invalidImage('Не удалось повернуть изображение');
        }

        imagedestroy($this->resource);
        $this->resource = $rotated;
        $this->width = imagesx($rotated);
        $this->height = imagesy($rotated);

        return $this;
    }

    public function flipHorizontal(): self
    {
        $this->load();

        if (!imageflip($this->resource, IMG_FLIP_HORIZONTAL)) {
            throw FileException::invalidImage('Не удалось отразить изображение');
        }

        return $this;
    }

    public function flipVertical(): self
    {
        $this->load();

        if (!imageflip($this->resource, IMG_FLIP_VERTICAL)) {
            throw FileException::invalidImage('Не удалось отразить изображение');
        }

        return $this;
    }

    public function watermark(string $watermarkPath, int $position = self::BOTTOM_RIGHT, int $padding = 10): self
    {
        $this->load();

        if (!is_file($watermarkPath)) {
            throw FileException::notFound($watermarkPath);
        }

        $wmResource = @imagecreatefromstring(file_get_contents($watermarkPath));

        if ($wmResource === false) {
            throw FileException::invalidImage('Некорректный файл водяного знака');
        }

        $wmWidth = imagesx($wmResource);
        $wmHeight = imagesy($wmResource);

        [$destX, $destY] = self::calculatePosition(
            $position,
            $this->width,
            $this->height,
            $wmWidth,
            $wmHeight,
            $padding
        );

        imagecopy($this->resource, $wmResource, $destX, $destY, 0, 0, $wmWidth, $wmHeight);
        imagedestroy($wmResource);

        return $this;
    }

    public function text(string $text, int $size = 12, string $color = '#000000', int $x = 10, int $y = 10): self
    {
        $this->load();

        $rgb = self::hexToRgb($color);
        $colorIndex = imagecolorallocate($this->resource, $rgb[0], $rgb[1], $rgb[2]);

        imagestring($this->resource, $size, $x, $y, $text, $colorIndex);

        return $this;
    }

    public function save(?string $path = null, string $format = 'png', int $quality = 90): string
    {
        $this->load();

        $path = $path ?? $this->path;

        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw FileException::directoryNotCreated($dir);
        }

        $result = match ($format) {
            'jpg', 'jpeg' => imagejpeg($this->resource, $path, $quality),
            'gif' => imagegif($this->resource, $path),
            'webp' => imagewebp($this->resource, $path, $quality),
            'bmp' => imagebmp($this->resource, $path),
            default => imagepng($this->resource, $path, (int)(9 - round($quality / 11.25))),
        };

        if ($result === false) {
            throw FileException::saveError('Не удалось сохранить изображение');
        }

        return $path;
    }

    public function saveAsJpeg(string $path, int $quality = 90): string
    {
        return $this->save($path, 'jpeg', $quality);
    }

    public function saveAsPng(string $path, int $quality = 90): string
    {
        return $this->save($path, 'png', $quality);
    }

    public function saveAsWebp(string $path, int $quality = 90): string
    {
        return $this->save($path, 'webp', $quality);
    }

    /**
     * Конвертирует изображение в WebP и сохраняет рядом с оригиналом.
     *
     * @param  int  $quality Качество WebP (0-100)
     * @return self
     *
     * @throws FileException Если не удалось конвертировать.
     */
    public function convertToWebp(int $quality = 100): self
    {
        $this->load();

        $dest = dirname($this->path) . DIRECTORY_SEPARATOR .
                pathinfo($this->path, PATHINFO_FILENAME) . '.webp';

        $this->saveAsWebp($dest, $quality);

        $this->path = $dest;

        return $this;
    }

    public function saveAsGif(string $path): string
    {
        return $this->save($path, 'gif');
    }

    public function toBase64(): string
    {
        $this->load();

        ob_start();
        imagepng($this->resource);
        $data = ob_get_clean();

        return 'data:' . $this->mimeType . ';base64,' . base64_encode($data);
    }

    public function getResource(): \GdImage
    {
        $this->load();

        return $this->resource;
    }

    public function __destruct()
    {
        if ($this->resource !== null) {
            imagedestroy($this->resource);
        }
    }

    public const TOP_LEFT = 1;
    public const TOP_CENTER = 2;
    public const TOP_RIGHT = 3;
    public const MIDDLE_LEFT = 4;
    public const MIDDLE_CENTER = 5;
    public const MIDDLE_RIGHT = 6;
    public const BOTTOM_LEFT = 7;
    public const BOTTOM_CENTER = 8;
    public const BOTTOM_RIGHT = 9;

    private function load(): void
    {
        if ($this->resource !== null) {
            return;
        }

        $info = @getimagesize($this->path);

        if ($info === false) {
            throw FileException::invalidImage('Некорректный файл изображения: ' . $this->path);
        }

        $this->width = $info[0];
        $this->height = $info[1];
        $this->mimeType = $info['mime'];

        $this->resource = match ($this->mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($this->path),
            'image/png' => @imagecreatefrompng($this->path),
            'image/gif' => @imagecreatefromgif($this->path),
            'image/webp' => @imagecreatefromwebp($this->path),
            'image/bmp' => @imagecreatefrombmp($this->path),
            default => throw FileException::invalidImage('Неподдерживаемый формат: ' . $this->mimeType),
        };

        if ($this->resource === false) {
            throw FileException::invalidImage('Не удалось загрузить изображение: ' . $this->path);
        }

        if ($this->mimeType === 'image/png') {
            imagealphablending($this->resource, true);
            imagesavealpha($this->resource, true);
        }
    }

    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function calculatePosition(int $position, int $canvasW, int $canvasH, int $objW, int $objH, int $padding): array
    {
        return match ($position) {
            self::TOP_LEFT => [$padding, $padding],
            self::TOP_CENTER => [(int)(($canvasW - $objW) / 2), $padding],
            self::TOP_RIGHT => [$canvasW - $objW - $padding, $padding],
            self::MIDDLE_LEFT => [$padding, (int)(($canvasH - $objH) / 2)],
            self::MIDDLE_CENTER => [(int)(($canvasW - $objW) / 2), (int)(($canvasH - $objH) / 2)],
            self::MIDDLE_RIGHT => [$canvasW - $objW - $padding, (int)(($canvasH - $objH) / 2)],
            self::BOTTOM_LEFT => [$padding, $canvasH - $objH - $padding],
            self::BOTTOM_CENTER => [(int)(($canvasW - $objW) / 2), $canvasH - $objH - $padding],
            self::BOTTOM_RIGHT => [$canvasW - $objW - $padding, $canvasH - $objH - $padding],
            default => [$padding, $padding],
        };
    }
}
