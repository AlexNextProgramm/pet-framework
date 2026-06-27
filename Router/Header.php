<?php

namespace Pet\Router;

class Header
{
    // ──────────────────────────────────────────────
    //  Константы Content-Type
    // ──────────────────────────────────────────────

    public const JSON = 'application/json';
    public const HTML = 'text/html';
    public const PLAIN = 'text/plain';
    public const XML = 'application/xml';
    public const FORM_URLENCODED = 'application/x-www-form-urlencoded';
    public const FORM_DATA = 'multipart/form-data';
    public const JAVASCRIPT = 'application/javascript';
    public const CSS = 'text/css';
    public const CSV = 'text/csv';

    // Изображения
    public const PNG = 'image/png';
    public const JPEG = 'image/jpeg';
    public const GIF = 'image/gif';
    public const WEBP = 'image/webp';
    public const SVG = 'image/svg+xml';
    public const ICO = 'image/x-icon';

    // Шрифты
    public const WOFF = 'font/woff';
    public const WOFF2 = 'font/woff2';
    public const TTF = 'font/ttf';
    public const OTF = 'font/otf';

    // ──────────────────────────────────────────────
    //  Константы Cache-Control
    // ──────────────────────────────────────────────

    public const CACHE_NO = 'no-store, no-cache, must-revalidate, max-age=0';
    public const CACHE_PUBLIC = 'public, max-age=%d';
    public const CACHE_PRIVATE = 'private, max-age=%d';
    public const CACHE_IMMUTABLE = 'public, max-age=%d, immutable';

    // ──────────────────────────────────────────────
    //  Константы CORS
    // ──────────────────────────────────────────────

    public const CORS_ANY = '*';
    public const CORS_DEFAULT_METHODS = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
    public const CORS_DEFAULT_HEADERS = 'Content-Type, Authorization, X-Requested-With';

    // ──────────────────────────────────────────────
    //  Константы Security
    // ──────────────────────────────────────────────

    public const CSP_DEFAULT = "default-src 'self'";
    public const XSS_PROTECTION = '1; mode=block';
    public const NO_SNIFF = 'nosniff';
    public const DENY_FRAME = 'DENY';
    public const SAMEORIGIN_FRAME = 'SAMEORIGIN';

    // ──────────────────────────────────────────────
    //  Установка заголовков
    // ──────────────────────────────────────────────

    /**
     * Устанавливает HTTP-заголовок.
     *
     * @param  string     $name    Имя заголовка
     * @param  string     $value   Значение заголовка
     * @param  bool       $replace Заменять предыдущий аналогичный заголовок (по умолчанию true)
     * @return void
     */
    public static function set(string $name, string $value, bool $replace = true): void
    {
        header("{$name}: {$value}", $replace);
    }

    /**
     * Устанавливает HTTP-статус ответа.
     *
     * @param  int    $code HTTP-код (см. константы HTTP::)
     * @return void
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Устанавливает несколько заголовков за один вызов.
     *
     * @param  array $headers Ассоциативный массив [имя => значение]
     * @return void
     */
    public static function setMany(array $headers): void
    {
        foreach ($headers as $name => $value) {
            self::set($name, $value);
        }
    }

    /**
     * Удаляет заголовок (если он ещё не отправлен).
     *
     * @param  string $name Имя заголовка
     * @return void
     */
    public static function remove(string $name): void
    {
        header_remove($name);
    }

    /**
     * Проверяет, отправлены ли уже заголовки.
     *
     * @return bool
     */
    public static function sent(): bool
    {
        return headers_sent();
    }

    /**
     * Возвращает список всех установленных (ещё не отправленных) заголовков.
     *
     * @return array
     */
    public static function list(): array
    {
        return headers_list();
    }

    // ──────────────────────────────────────────────
    //  Content-Type
    // ──────────────────────────────────────────────

    /**
     * Устанавливает заголовок Content-Type.
     *
     * @param  string $mime  MIME-тип (можно использовать константы класса)
     * @param  string $charset Кодировка (по умолчанию utf-8)
     * @return void
     */
    public static function type(string $mime, string $charset = 'utf-8'): void
    {
        self::set('Content-Type', "{$mime}; charset={$charset}");
    }

    /**
     * Устанавливает Content-Type: application/json.
     *
     * @return void
     */
    public static function json(): void
    {
        self::type(self::JSON);
    }

    /**
     * Устанавливает Content-Type: text/html.
     *
     * @return void
     */
    public static function html(): void
    {
        self::type(self::HTML);
    }

    /**
     * Устанавливает Content-Type: text/plain.
     *
     * @return void
     */
    public static function plain(): void
    {
        self::type(self::PLAIN);
    }

    // ──────────────────────────────────────────────
    //  Кэширование
    // ──────────────────────────────────────────────

    /**
     * Разрешает кэширование ответа на указанное количество секунд.
     *
     * @param  int  $seconds Время кэширования (по умолчанию 3600)
     * @param  bool $public  Публичный кэш (true) или приватный (false)
     * @return void
     */
    public static function cache(int $seconds = 3600, bool $public = true): void
    {
        $directive = $public ? self::CACHE_PUBLIC : self::CACHE_PRIVATE;
        self::set('Cache-Control', sprintf($directive, $seconds));
        self::set('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
    }

    /**
     * Запрещает кэширование ответа.
     *
     * @return void
     */
    public static function noCache(): void
    {
        self::set('Cache-Control', self::CACHE_NO);
        self::set('Pragma', 'no-cache');
        self::set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    /**
     * Устанавливает кэширование с флагом immutable (для статики).
     *
     * @param  int $seconds Время кэширования (по умолчанию 31536000 — 1 год)
     * @return void
     */
    public static function cacheImmutable(int $seconds = 31536000): void
    {
        self::set('Cache-Control', sprintf(self::CACHE_IMMUTABLE, $seconds));
        self::set('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
    }

    /**
     * Устанавливает ETag для ответа.
     *
     * @param  string $value Значение ETag
     * @return void
     */
    public static function etag(string $value): void
    {
        self::set('ETag', '"' . $value . '"');
    }

    /**
     * Устанавливает заголовок Last-Modified.
     *
     * @param  int $timestamp Unix-метка времени последнего изменения
     * @return void
     */
    public static function lastModified(int $timestamp): void
    {
        self::set('Last-Modified', gmdate('D, d M Y H:i:s', $timestamp) . ' GMT');
    }

    // ──────────────────────────────────────────────
    //  CORS
    // ──────────────────────────────────────────────

    /**
     * Устанавливает заголовки CORS для кросс-доменных запросов.
     *
     * @param  string      $origin         Разрешённый источник (по умолчанию '*')
     * @param  string      $methods        Разрешённые HTTP-методы
     * @param  string      $headers        Разрешённые заголовки
     * @param  bool        $credentials    Разрешить передачу учетных данных
     * @param  int|null    $maxAge         Время кэширования preflight-запроса (сек)
     * @return void
     */
    public static function cors(
        string $origin = self::CORS_ANY,
        string $methods = self::CORS_DEFAULT_METHODS,
        string $headers = self::CORS_DEFAULT_HEADERS,
        bool $credentials = false,
        ?int $maxAge = null
    ): void {
        self::set('Access-Control-Allow-Origin', $origin);

        if ($credentials) {
            self::set('Access-Control-Allow-Credentials', 'true');
        }

        if ($maxAge !== null) {
            self::set('Access-Control-Max-Age', (string) $maxAge);
        }

        // Эти заголовки имееют смысл в основном для preflight (OPTIONS)
        self::set('Access-Control-Allow-Methods', $methods);
        self::set('Access-Control-Allow-Headers', $headers);
    }

    /**
     * Устанавливает заголовок Allow для указания разрешённых методов.
     *
     * @param  string ...$methods HTTP-методы (GET, POST, ...)
     * @return void
     */
    public static function allow(string ...$methods): void
    {
        self::set('Allow', implode(', ', $methods));
    }

    // ──────────────────────────────────────────────
    //  Security
    // ──────────────────────────────────────────────

    /**
     * Устанавливает Content-Security-Policy.
     *
     * @param  string $policy Политика (по умолчанию "default-src 'self'")
     * @return void
     */
    public static function csp(string $policy = self::CSP_DEFAULT): void
    {
        self::set('Content-Security-Policy', $policy);
    }

    /**
     * Устанавливает X-Content-Type-Options: nosniff.
     *
     * @return void
     */
    public static function noSniff(): void
    {
        self::set('X-Content-Type-Options', self::NO_SNIFF);
    }

    /**
     * Устанавливает X-Frame-Options.
     *
     * @param  string $value DENY или SAMEORIGIN (по умолчанию DENY)
     * @return void
     */
    public static function frame(string $value = self::DENY_FRAME): void
    {
        self::set('X-Frame-Options', $value);
    }

    /**
     * Устанавливает X-XSS-Protection.
     *
     * @param  string $value Значение заголовка (по умолчанию "1; mode=block")
     * @return void
     */
    public static function xss(string $value = self::XSS_PROTECTION): void
    {
        self::set('X-XSS-Protection', $value);
    }

    /**
     * Устанавливает базовые security-заголовки одним вызовом.
     *
     * @param  bool $includeCsp Включить Content-Security-Policy
     * @return void
     */
    public static function secure(bool $includeCsp = true): void
    {
        self::noSniff();
        self::frame();
        self::xss();

        if ($includeCsp) {
            self::csp();
        }
    }

    /**
     * Устанавливает Strict-Transport-Security (HSTS).
     *
     * @param  int  $maxAge       Время в секундах (по умолчанию 31536000 — 1 год)
     * @param  bool $includeSubdomains Включить поддомены
     * @return void
     */
    public static function hsts(int $maxAge = 31536000, bool $includeSubdomains = true): void
    {
        $value = "max-age={$maxAge}";
        if ($includeSubdomains) {
            $value .= '; includeSubDomains';
        }
        self::set('Strict-Transport-Security', $value);
    }

    // ──────────────────────────────────────────────
    //  Скачивание файлов (Content-Disposition)
    // ──────────────────────────────────────────────

    /**
     * Устанавливает заголовки для принудительного скачивания файла.
     *
     * @param  string $filename Имя файла, которое увидит пользователь
     * @param  string $mime     MIME-тип файла
     * @return void
     */
    public static function download(string $filename, string $mime = self::OCTET_STREAM): void
    {
        self::set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        self::set('Content-Type', $mime);
        self::set('Content-Transfer-Encoding', 'binary');
        self::noCache();
    }

    /**
     * Устанавливает заголовки для инлайн-отображения файла (в браузере).
     *
     * @param  string $filename Имя файла
     * @param  string $mime     MIME-тип файла
     * @return void
     */
    public static function inline(string $filename, string $mime): void
    {
        self::set('Content-Disposition', 'inline; filename="' . $filename . '"');
        self::set('Content-Type', $mime);
    }

    // ──────────────────────────────────────────────
    //  Дополнительные Content-Type константы
    // ──────────────────────────────────────────────

    /** @var string application/octet-stream */
    public const OCTET_STREAM = 'application/octet-stream';

    /** @var string application/pdf */
    public const PDF = 'application/pdf';

    /** @var string application/zip */
    public const ZIP = 'application/zip';

    /** @var string application/gzip */
    public const GZIP = 'application/gzip';

    // ──────────────────────────────────────────────
    //  Редирект
    // ──────────────────────────────────────────────

    /**
     * Устанавливает заголовок Location для редиректа.
     *
     * @param  string $url URL для перенаправления
     * @return void
     */
    public static function location(string $url): void
    {
        self::set('Location', $url);
    }

    /**
     * Устанавливает заголовок Refresh (редирект с задержкой).
     *
     * @param  string $url     URL для перенаправления
     * @param  int    $seconds Задержка в секундах
     * @return void
     */
    public static function refresh(string $url, int $seconds = 0): void
    {
        self::set('Refresh', "{$seconds}; url={$url}");
    }

    // ──────────────────────────────────────────────
    //  Прочие полезные заголовки
    // ──────────────────────────────────────────────

    /**
     * Устанавливает Content-Length.
     *
     * @param  int $bytes Размер в байтах
     * @return void
     */
    public static function length(int $bytes): void
    {
        self::set('Content-Length', (string) $bytes);
    }

    /**
     * Устанавливает заголовок Link (например, для preload/prefetch).
     *
     * @param  string $url       URL ресурса
     * @param  string $relation  Отношение (preload, dns-prefetch, preconnect, ...)
     * @param  string|null $as   Тип ресурса для preload (style, script, image, ...)
     * @return void
     */
    public static function link(string $url, string $relation, ?string $as = null): void
    {
        $value = "<{$url}>; rel={$relation}";
        if ($as !== null) {
            $value .= "; as={$as}";
        }
        self::set('Link', $value);
    }

    /**
     * Устанавливает Retry-After (для 503 Service Unavailable и т.п.).
     *
     * @param  int $seconds Количество секунд для ожидания
     * @return void
     */
    public static function retryAfter(int $seconds): void
    {
        self::set('Retry-After', (string) $seconds);
    }

    /**
     * Устанавливает заголовок Vary.
     *
     * @param  string ...$headers Заголовки, по которым варьируется ответ
     * @return void
     */
    public static function vary(string ...$headers): void
    {
        self::set('Vary', implode(', ', $headers));
    }
}
