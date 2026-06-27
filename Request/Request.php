<?php

namespace Pet\Request;

use Pet\File\File;
use Pet\File\FileCollection;
use Pet\Tools\Tools;

class Request
{
    public static array $attribute = [];
    public static array $parametr = [];
    public static array $levels = [];
    public static string $original = '';
    public $header = [];
    public $path;

    /** @var FileCollection|null Коллекция загруженных файлов */
    private static ?FileCollection $uploadedFiles = null;


    public function __construct()
    {
        self::$attribute = $this->input();
        $this->path = $this->getURI();
        $this->parsingHeaders();
        self::$uploadedFiles = null;
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    public function getURI()
    {
        $path = str_contains($_SERVER['REQUEST_URI'], '?') ? explode('?', $_SERVER['REQUEST_URI'])[0] :
            $_SERVER['REQUEST_URI'];

        self::$original = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        self::$levels = array_values(array_filter(explode('/', trim($path, '/'))));

        return $path != '/'? Tools::strRep(strlen($path) - 1, '', $path, '/'): $path;
    }

    /**
     * input
     *
     * @param  string|null $name
     * @return array|string|null
     */
    public function input(string|null $name = null): array|string|null
    {
        if(!empty(self::$attribute)){
            return key_exists($name, self::$attribute) ? self::$attribute[$name]: self::$attribute;
        }
        $REQUEST = $this->parsing();
        if (!$name) return $REQUEST;
        return key_exists($name, $REQUEST) ? $REQUEST[$name] : null;
    }


    private function parsing()
    {
        $REQUEST = array_merge($_GET, $_POST, $_FILES);
        $decode = [];
        $input = file_get_contents('php://input');
        $ctype = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($ctype, 'json') && !empty($input)) $decode = Tools::jsonDe($input);
        return array_merge($REQUEST, $decode);
    }

    /**
     * Возвращает загруженный файл как объект File или коллекцию FileCollection.
     *
     * @param  string|null $name Имя поля в $_FILES
     * @return File|FileCollection|array|null
     */
    public function file(?string $name = null): File|FileCollection|array|null
    {
        if ($_FILES === []) {
            return $name !== null ? null : [];
        }

        if (self::$uploadedFiles === null) {
            self::$uploadedFiles = FileCollection::fromUploadedFiles($_FILES);
        }

        if ($name === null) {
            return self::$uploadedFiles;
        }

        if (!isset($_FILES[$name])) {
            return null;
        }

        if (is_array($_FILES[$name]['name'] ?? null)) {
            return FileCollection::fromUploadedFiles($_FILES[$name]);
        }

        if (($_FILES[$name]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            return File::fromUpload($_FILES[$name]);
        }

        return null;
    }

    /**
     * Проверяет, был ли загружен файл с указанным именем.
     *
     * @param  string $name
     * @return bool
     */
    public function hasFile(string $name): bool
    {
        return isset($_FILES[$name]) && ($_FILES[$name]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Возвращает все загруженные файлы как массив сырых $_FILES.
     *
     * @return array
     */
    public function allFiles(): array
    {
        return $_FILES;
    }

    private function parsingHeaders(){
        $header =  getallheaders();
        foreach($header as $key => $val) $this->header[strtolower($key)] = strtolower($val);
    }


    public function ip(): string|false {
        $ip_keys = [
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return false;
    }

    /**
     * Возвращает путь запроса (URI без query-строки).
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Возвращает значение заголовка запроса (регистронезависимо).
     *
     * @param  string $name Имя заголовка
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $key = strtolower($name);
        return $this->header[$key] ?? null;
    }

    /**
     * Устанавливает параметр маршрута (из flexible- или wildcard-маршрутов).
     *
     * @param  string $name  Имя параметра
     * @param  string $value Значение параметра
     * @return void
     */
    public static function setParameter(string $name, string $value): void
    {
        self::$parametr[$name] = $value;
    }

    /**
     * Возвращает параметр маршрута по имени.
     *
     * @param  string      $name Имя параметра
     * @return string|null
     */
    public static function getParameter(string $name): ?string
    {
        return self::$parametr[$name] ?? null;
    }
}
