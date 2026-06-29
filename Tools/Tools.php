<?php

namespace Pet\Tools;

use Exception;

class Tools {


    /**
     * jsonDe
     *
     * @param  mixed $value
     * @param  mixed $bool
     * @return array
     */
    static function jsonDe(string $value, bool $bool = true): array|object {
        try {
            return json_decode($value, $bool);
        } catch (Exception $e) {
            return [];
        }
    }


    /**
     * strRep
     *
     * @param  mixed $i
     * @param  mixed $seporator
     * @param  mixed $string
     * @param  mixed $if
     * @return string
     */
    static function strRep($i, $seporator, &$string, $if = null): string {
        $string = str_split($string);
        if ($if && $string[$i] === $if) $string[$i] = $seporator;

        if (!$if) $string[$i] = $seporator;
        return implode("", $string);
    }

    /**
     * array_implode
     *
     * @param  mixed $seporator
     * @param  mixed $arrKeyValue
     * @param  mixed $between [key] [val]
     * @return string
     * 
     */
    static function array_implode(string $seporator,  array $arrKeyValue, string $between = '[key]=[val]', $callback = null): string {

        return implode($seporator, array_map(
            fn($v, $k)=> $callback?$callback($v, $k, $between):str_replace(['[val]', '[key]'],[$v, $k], $between),
            $arrKeyValue,
            array_keys($arrKeyValue)
        ));
    }

    /**
     * is_assos
     *
     * @param  array $array
     * @return string "index"|"gibrid"|"assos"
     */
    static function is_assos(array $array): string
    {
        $keys = array_keys($array);
        $str = implode("", $keys);
        
        if (is_numeric($str)) return 'index';
        
        $hasString = false;
        $hasInt = false;
        
        foreach ($keys as $key) {
            if (is_string($key)) {
                $hasString = true;
            } else {
                $hasInt = true;
            }
        }
        
        if ($hasInt && $hasString) {
            return 'gibrid';
        }
        
        return 'assos';
    }

    /**
     * strRepalceFile
     *
     * @param  array $search
     * @param  array|string $replace
     * @param  string $path
     * @param  string $save
     * @return string
     */
    static function strRepalceFile(array|string $search, array|string $replace, string $path, ?string $save = null): string|false {
        if (!file_exists($path)) return false;
        $file = file_get_contents($path);
        $file = str_replace($search, $replace, $file);
        if ($save) file_put_contents($save, $file);
        return $file;
    }


    public static function filter(array $data, callable $callback): array
    {
        foreach ($data as $k => $v){
            $data[$k] = $callback($k, $v);
        }
        return $data;
    }

    public static function scan(string $path, callable $callback, $isPath = false):void
    {
        foreach (scandir($path) as $file) {
            if (in_array($file, ['..', '.'])) continue;
            $name =  $path . DS . $file;
            if ($isPath) {
                if (is_dir($name)) $callback($name, false);
                if (file_exists($name) && is_readable($name)) $callback(false, $name);
            } else {
                if (is_dir($name)) $callback($file, false);
                if (file_exists($name) && is_readable($name)) $callback(false, $file);
            }
        }
    }

    /**
     * Создание миниатюры (thumb) изображения.
     *
     * Поддерживает JPEG, PNG, GIF, WebP, ICO.
     * При crop=true вырезает центральную область нужного размера.
     * При crop=false ресайзит с сохранением пропорций, вписывая в targetWidth x targetHeight.
     *
     * @param  string      $src              Путь к исходному файлу
     * @param  string      $dest             Путь для сохранения результата
     * @param  int         $targetWidth      Целевая ширина
     * @param  int|null    $targetHeight     Целевая высота (если null — высчитывается по пропорции)
     * @param  int         $x                Смещение по X от исходного изображения (при crop)
     * @param  int         $y                Смещение по Y от исходного изображения (при crop)
     * @param  bool        $crop             Вырезать центральную область (true) или ресайзить с пропорциями (false)
     * @param  int         $dst_x            Смещение по X на целевом изображении
     * @param  int         $dst_y            Смещение по Y на целевом изображении
     * @param  int         $quality          Качество для JPEG/WebP (0-100)
     * @param  string|null $outputFormat     Принудительный формат вывода ('webp' или null)
     * @return bool|null                     true при успехе, null при ошибке
     */
    static function thumb(
        string $src,
        string $dest,
        int $targetWidth,
        ?int $targetHeight = null,
        int $x = 0,
        int $y = 0,
        bool $crop = false,
        int $dst_x = 0,
        int $dst_y = 0,
        int $quality = 100,
        ?string $outputFormat = null
    ) {
        $image_handlers = [
            2 => [
                'load'    => 'imagecreatefromjpeg',
                'save'    => 'imagejpeg',
                'quality' => $quality
            ],
            3 => [
                'load'    => 'imagecreatefrompng',
                'save'    => 'imagepng',
                'quality' => 0
            ],
            4 => [
                'load' => 'imagecreatefromgif',
                'save' => 'imagegif'
            ],
            18 => [
                'load'    => 'imagecreatefromwebp',
                'save'    => 'imagewebp',
                'quality' => $quality
            ]
        ];

        $type = @exif_imagetype($src);

        // ICO — просто копируем
        if ($type === 17) {
            return copy($src, $dest);
        }

        if (!$type || !isset($image_handlers[$type])) {
            return null;
        }

        $image = call_user_func($image_handlers[$type]['load'], $src);
        if (!$image) {
            return null;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        // Если targetWidth = 0 — конвертация без изменения размера
        if ($targetWidth === 0) {
            $targetWidth  = $width;
            $targetHeight = $height;
            $srcWidth     = $width;
            $srcHeight    = $height;
            $crop         = false;
        } else {
            // Если исходник меньше или равен target — не увеличиваем
            $targetWidth  = min($targetWidth, $width);
            $targetHeight = $targetHeight !== null ? min($targetHeight, $height) : null;
        }

        if ($crop) {
            // Режим кропа: вырезаем область размером targetWidth x targetHeight
            // Если targetHeight не указан — делаем квадрат по targetWidth
            if ($targetHeight === null) {
                $targetHeight = $targetWidth;
            }

            // Центрируем область вырезания, если x/y не заданы явно
            if ($x === 0 && $y === 0) {
                $x = (int)(($width  - $targetWidth)  / 2);
                $y = (int)(($height - $targetHeight) / 2);
            }

            $srcWidth  = $targetWidth;
            $srcHeight = $targetHeight;
        } else {
            // Режим ресайза с сохранением пропорций
            $ratio = $width / $height;

            if ($targetHeight === null) {
                // Только ширина задана — высота по пропорции
                $targetHeight = (int)round($targetWidth / $ratio);
            } elseif ($targetWidth === null || $targetWidth === 0) {
                // Только высота задана — ширина по пропорции
                $targetWidth = (int)round($targetHeight * $ratio);
            } else {
                // Заданы оба — вписываем в контейнер
                $ratioW = $width  / $targetWidth;
                $ratioH = $height / $targetHeight;

                if ($ratioW > $ratioH) {
                    $targetHeight = (int)round($targetWidth / $ratio);
                } else {
                    $targetWidth  = (int)round($targetHeight * $ratio);
                }
            }

            $srcWidth  = $width;
            $srcHeight = $height;
        }

        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

        // Сохраняем прозрачность для PNG, GIF и WebP
        if (in_array($type, [3, 4, 18], true)) {
            imagecolortransparent(
                $thumbnail,
                imagecolorallocate($thumbnail, 0, 0, 0)
            );

            if (in_array($type, [3, 18], true)) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
        }

        imagecopyresampled(
            $thumbnail,
            $image,
            $dst_x,
            $dst_y,
            $x,
            $y,
            $targetWidth,
            $targetHeight,
            $srcWidth,
            $srcHeight
        );

        // Принудительный экспорт в WebP
        if ($outputFormat === 'webp' && function_exists('imagewebp')) {
            if (!preg_match('/\.webp$/i', $dest)) {
                $dest = preg_replace('/\.[^.]+$/', '.webp', $dest) ?? ($dest . '.webp');
            }

            return imagewebp($thumbnail, $dest, $quality);
        }

        return call_user_func(
            $image_handlers[$type]['save'],
            $thumbnail,
            $dest,
            $image_handlers[$type]['quality'] ?? null
        );
    }
}