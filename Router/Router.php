<?php

namespace Pet\Router;

use Pet\Request\Request;

/**
 * Router — основной маршрутизатор приложения.
 *
 * Позволяет регистрировать маршруты с поддержкой:
 *   - HTTP-методов (GET, POST, PUT, DELETE, OPTIONS)
 *   - Wildcard-маршрутов (/*)
 *   - Flexible-параметров ({id}, {slug})
 *   - Middleware (через наследование от Middleware)
 *   - AJAX-событий
 *   - Именованных маршрутов и групп
 *
 * @method static Router get(string $path, callable|string|array ...$callback)
 * @method static Router post(string $path, callable|string|array ...$callback)
 * @method static Router put(string $path, callable|string|array ...$callback)
 * @method static Router delete(string $path, callable|string|array ...$callback)
 * @method static Router options(string $path, callable|string|array ...$callback)
 */
class Router extends Middleware
{
    /** @var string Директория публичных файлов (переопределяется в конфиге) */
    public const PUBLIC_DIR = PUBLIC_DIR;

    /** @var array<int, array> Все зарегистрированные маршруты */
    private static array $routes = [];

    /** @var callable|string|array|null Fallback-обработчик для 404 */
    private static mixed $fallback = null;

    /** @var int ID последнего добавленного маршрута */
    private static int $lastRouteId = 0;

    /** @var array<string, callable|string|array> Зарегистрированные AJAX-события */
    private static array $events = [];

    /** @var array<string> HTTP-методы, для которых разрешён AJAX */
    private const AJAX_METHODS = ['POST'];

    /** @var string Регулярка для flexible-параметров */
    private const FLEXIBLE_PARAM_REGEX = '|{([a-z]{1,})}|';

    /** @var string Регулярка для значения flexible-параметра */
    private const FLEXIBLE_VALUE_REGEX = '([a-zA-Z0-9?_-]+)';

    /**
     * ---------------------------------------------------------------------------
     *  Регистрация маршрутов
     * ---------------------------------------------------------------------------
     */

    /**
     * Регистрирует GET-маршрут.
     *
     * @param  string                    $path     URI-шаблон
     * @param  callable|string|array     ...$callback Обработчик(и)
     * @return static
     */
    public static function get(string $path, callable|string|array ...$callback): static
    {
        return self::addRoute('GET', $path, $callback);
    }

    /**
     * Регистрирует POST-маршрут.
     *
     * @param  string                    $path     URI-шаблон
     * @param  callable|string|array     ...$callback Обработчик(и)
     * @return static
     */
    public static function post(string $path, callable|string|array ...$callback): static
    {
        return self::addRoute('POST', $path, $callback);
    }

    /**
     * Регистрирует PUT-маршрут.
     *
     * @param  string                    $path     URI-шаблон
     * @param  callable|string|array     ...$callback Обработчик(и)
     * @return static
     */
    public static function put(string $path, callable|string|array ...$callback): static
    {
        return self::addRoute('PUT', $path, $callback);
    }

    /**
     * Регистрирует DELETE-маршрут.
     *
     * @param  string                    $path     URI-шаблон
     * @param  callable|string|array     ...$callback Обработчик(и)
     * @return static
     */
    public static function delete(string $path, callable|string|array ...$callback): static
    {
        return self::addRoute('DELETE', $path, $callback);
    }

    /**
     * Регистрирует OPTIONS-маршрут.
     *
     * @param  string                    $path     URI-шаблон
     * @param  callable|string|array     ...$callback Обработчик(и)
     * @return static
     */
    public static function options(string $path, callable|string|array ...$callback): static
    {
        return self::addRoute('OPTIONS', $path, $callback);
    }

    /**
     * Добавляет маршрут во внутренний реестр.
     *
     * @param  string                    $method   HTTP-метод
     * @param  string                    $path     URI-шаблон
     * @param  array                     $callback Массив обработчиков
     * @return static
     */
    private static function addRoute(string $method, string $path, array $callback): static
    {
        $method = strtoupper($method);

        // Проверка на конфликт с существующими маршрутами
        self::detectConflict($method, $path);

        self::$routes[] = [
            'path'     => $path,
            'method'   => $method,
            'callback' => $callback,
        ];

        self::$lastRouteId = array_key_last(self::$routes);

        return new static();
    }

    /**
     * Проверяет, не конфликтует ли новый маршрут с уже зарегистрированными.
     *
     * Конфликт возникает, когда два маршрута с одним HTTP-методом
     * могут совпасть с одним и тем же URL. Это приводит к неопределённости:
     * какой из них должен обработать запрос?
     *
     * Типы конфликтов:
     *   1. Точный vs точный       — /user/list и /user/list (дубликат)
     *   2. Точный vs flexible     — /user/42 и /user/{id} (точный никогда не сработает,
     *                                если flexible зарегистрирован раньше)
     *   3. Flexible vs flexible   — /user/{id} и /user/{slug} (оба подходят под /user/abc)
     *   4. Wildcard vs flexible   — /user/* и /user/{id} (wildcard перехватывает)
     *   5. Wildcard vs wildcard   — /user/* и /user/* (дубликат)
     *
     * @param  string $method HTTP-метод
     * @param  string $path   Шаблон нового маршрута
     * @return void
     * @throws \RuntimeException Если обнаружен конфликт
     */
    private static function detectConflict(string $method, string $path): void
    {
        $isNewWildcard = self::isWildcardRoute($path);
        $newHasFlexible = !$isNewWildcard && preg_match(self::FLEXIBLE_PARAM_REGEX, $path);

        foreach (self::$routes as $index => $existing) {
            if ($existing['method'] !== $method) {
                continue;
            }

            $existingPath = $existing['path'];
            $isExistingWildcard = self::isWildcardRoute($existingPath);
            $existingHasFlexible = !$isExistingWildcard && preg_match(self::FLEXIBLE_PARAM_REGEX, $existingPath);

            // Случай 1: точный vs точный (дубликат)
            if (!$isNewWildcard && !$newHasFlexible && !$isExistingWildcard && !$existingHasFlexible) {
                if ($path === $existingPath) {
                    throw new \RuntimeException(
                        "Route conflict: exact duplicate [{$method}] {$path} (route #{$index})"
                    );
                }
                continue;
            }

            // Случай 5: wildcard vs wildcard (дубликат)
            if ($isNewWildcard && $isExistingWildcard) {
                if ($path === $existingPath) {
                    throw new \RuntimeException(
                        "Route conflict: duplicate wildcard [{$method}] {$path} (route #{$index})"
                    );
                }
                continue;
            }

            // Для остальных случаев конвертируем оба шаблона в regex
            $newRegex = self::patternToRegex($path, $isNewWildcard, $newHasFlexible);
            $existingRegex = self::patternToRegex($existingPath, $isExistingWildcard, $existingHasFlexible);

            // Случай 2: точный vs flexible
            // Точный путь совпадает с regex flexible — flexible перехватит
            if (!$isNewWildcard && !$newHasFlexible && $existingHasFlexible) {
                if (preg_match($existingRegex, $path)) {
                    throw new \RuntimeException(
                        "Route conflict: exact route [{$method}] {$path} will never match — " .
                        "it is intercepted by flexible route [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }

            // Случай 2 (обратный): flexible vs точный
            if ($newHasFlexible && !$isExistingWildcard && !$existingHasFlexible) {
                if (preg_match($newRegex, $existingPath)) {
                    throw new \RuntimeException(
                        "Route conflict: flexible route [{$method}] {$path} intercepts " .
                        "exact route [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }

            // Случай 3: flexible vs flexible
            if ($newHasFlexible && $existingHasFlexible) {
                // Проверяем, могут ли оба regex совпасть с одним URL
                // Для этого проверяем, совпадает ли структура сегментов
                $newSegments = explode('/', trim($path, '/'));
                $existingSegments = explode('/', trim($existingPath, '/'));

                if (count($newSegments) !== count($existingSegments)) {
                    continue; // разное количество сегментов — не конфликтуют
                }

                // Сравниваем посегментно: если оба не-flexible сегмента различаются — не конфликт
                $conflict = true;
                foreach ($newSegments as $i => $newSeg) {
                    $isNewFlex = preg_match(self::FLEXIBLE_PARAM_REGEX, $newSeg);
                    $isExistFlex = preg_match(self::FLEXIBLE_PARAM_REGEX, $existingSegments[$i]);

                    if (!$isNewFlex && !$isExistFlex && $newSeg !== $existingSegments[$i]) {
                        $conflict = false;
                        break;
                    }
                }

                if ($conflict) {
                    throw new \RuntimeException(
                        "Route conflict: flexible routes [{$method}] {$path} and " .
                        "[{$method}] {$existingPath} (route #{$index}) can match the same URL"
                    );
                }
                continue;
            }

            // Случай 4: wildcard vs flexible
            if ($isNewWildcard && $existingHasFlexible) {
                $wcPrefix = rtrim(substr($path, 0, -1), '/'); // /user/* -> /user
                if (preg_match($existingRegex, $wcPrefix . '/test')) {
                    throw new \RuntimeException(
                        "Route conflict: wildcard [{$method}] {$path} intercepts " .
                        "flexible route [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }

            // Случай 4 (обратный): flexible vs wildcard
            if ($newHasFlexible && $isExistingWildcard) {
                $wcPrefix = rtrim(substr($existingPath, 0, -1), '/');
                if (preg_match($newRegex, $wcPrefix . '/test')) {
                    throw new \RuntimeException(
                        "Route conflict: flexible route [{$method}] {$path} is intercepted " .
                        "by wildcard [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }

            // Точный vs wildcard: точный /user/list и wildcard /user/*
            if (!$isNewWildcard && !$newHasFlexible && $isExistingWildcard) {
                $wcPrefix = rtrim(substr($existingPath, 0, -1), '/');
                if (str_starts_with($path, $wcPrefix === '' ? '/' : $wcPrefix . '/')) {
                    throw new \RuntimeException(
                        "Route conflict: exact route [{$method}] {$path} will never match — " .
                        "it is intercepted by wildcard [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }

            // Точный vs wildcard (обратный): wildcard регистрируется после точного
            if ($isNewWildcard && !$isExistingWildcard && !$existingHasFlexible) {
                $wcPrefix = rtrim(substr($path, 0, -1), '/');
                if (str_starts_with($existingPath, $wcPrefix === '' ? '/' : $wcPrefix . '/')) {
                    throw new \RuntimeException(
                        "Route conflict: wildcard [{$method}] {$path} intercepts " .
                        "exact route [{$method}] {$existingPath} (route #{$index})"
                    );
                }
                continue;
            }
        }
    }

    /**
     * Конвертирует шаблон маршрута в регулярное выражение для проверки конфликтов.
     *
     * @param  string $path        Шаблон маршрута
     * @param  bool   $isWildcard  Является ли wildcard-маршрутом
     * @param  bool   $hasFlexible Содержит ли flexible-параметры
     * @return string
     */
    private static function patternToRegex(string $path, bool $isWildcard, bool $hasFlexible): string
    {
        if ($isWildcard) {
            $prefix = rtrim(substr($path, 0, -1), '/');
            return '#^' . preg_quote($prefix, '#') . '(/.+)?$#';
        }

        if ($hasFlexible) {
            $regex = preg_replace(self::FLEXIBLE_PARAM_REGEX, self::FLEXIBLE_VALUE_REGEX, $path);
            return '#^' . $regex . '$#';
        }

        return '#^' . preg_quote($path, '#') . '$#';
    }

    /**
     * ---------------------------------------------------------------------------
     *  Модификаторы маршрутов (fluent)
     * ---------------------------------------------------------------------------
     */

    /**
     * Задаёт имя маршруту.
     *
     * @param  string $name
     * @return $this
     */
    public function name(string $name): static
    {
        self::$routes[self::$lastRouteId]['name'] = $name;
        return $this;
    }

    /**
     * Задаёт группу маршруту.
     *
     * @param  string $group
     * @return $this
     */
    public function group(string $group): static
    {
        self::$routes[self::$lastRouteId]['group'] = $group;
        return $this;
    }

    /**
     * Регистрирует fallback-обработчик для ненайденных маршрутов (404).
     *
     * @param  callable|string|array $callback Обработчик
     * @return void
     */
    public static function fallback(callable|string|array $callback): void
    {
        self::$fallback = $callback;
    }

    /**
     * ---------------------------------------------------------------------------
     *  События (AJAX)
     * ---------------------------------------------------------------------------
     */

    /**
     * Регистрирует AJAX-событие.
     *
     * @param  string                    $key    Ключ события (заголовок запроса)
     * @param  callable|string|array     $action Обработчик
     * @return void
     */
    public static function event(string $key, callable|string|array $action): void
    {
        self::$events[$key] = $action;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Запуск маршрутизации
     * ---------------------------------------------------------------------------
     */

    /**
     * Запускает обработку входящего запроса.
     *
     * Последовательно перебирает зарегистрированные маршруты,
     * применяет middleware, вызывает обработчики и отправляет ответ.
     *
     * @return never
     */
    public static function init(): never
    {
        ob_start();

        $request = request();
        $routeMatched = false;

        foreach (self::getOrderedRoutes() as $route) {
            // AJAX-события обрабатываются до проверки метода
            if (self::isAjaxMethod($request) && self::handleAjaxEvent($request)) {
                // handleAjaxEvent уже завершил выполнение через Response::die()
            }

            // Фильтрация по HTTP-методу
            if ($route['method'] !== $request->getMethod()) {
                continue;
            }

            // Если маршрут уже найден — пропускаем остальные
            if ($routeMatched) {
                continue;
            }

            // Проверка совпадения пути (wildcard, flexible, точное)
            if (!self::matchRoute($request, $route)) {
                continue;
            }

            // Выполнение middleware
            if (isset($route['middleware'])) {
                $middlewareResult = self::executeMiddleware($route, $request);

                if ($middlewareResult === false) {
                    break;
                }
            }

            // Выполнение обработчиков маршрута
            $results = self::executeCallbacks($route['callback'], $request);

            // Отправка ответа
            self::sendResponse($results);

            $routeMatched = true;
        }

        // 404 — маршрут не найден
        if (!$routeMatched) {
            Header::status(HTTP::NOT_FOUND);
            if (self::$fallback !== null) {
                $results = ( new Invoker())->call(self::$fallback, $request);
                if (!empty($results)) {
                    self::sendResponse($results);
                }
            }
        }

        ob_end_flush();
        exit;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Middleware
     * ---------------------------------------------------------------------------
     */

    /**
     * Выполняет middleware для маршрута.
     *
     * @param  array   $route   Данные маршрута
     * @param  Request $request Объект запроса
     * @return bool|null         false — прервать цепочку, null/true — продолжать
     */
    private static function executeMiddleware(array $route, Request $request): bool|null
    {
        if (isset($route['isManyMiddle']) && $route['isManyMiddle'] === true) {
            // Множественные middleware
            foreach ($route['middleware'] as $middleware) {
                $result = (new Invoker())->call($middleware, $request);

                if ($result === false) {
                    return false;
                }
            }

            return null;
        }

        // Одиночный middleware
        return (new Invoker())->call($route['middleware'], $request);
    }

    /**
     * ---------------------------------------------------------------------------
     *  Обработка AJAX
     * ---------------------------------------------------------------------------
     */

    /**
     * Проверяет, является ли метод запроса AJAX-совместимым.
     *
     * @param  Request $request
     * @return bool
     */
    private static function isAjaxMethod(Request $request): bool
    {
        return in_array($request->getMethod(), self::AJAX_METHODS, true);
    }

    /**
     * Обрабатывает AJAX-событие, если найден соответствующий заголовок.
     *
     * @param  Request $request
     * @return bool     true — событие обработано и выполнение завершено
     */
    private static function handleAjaxEvent(Request $request): bool
    {
        foreach (self::$events as $key => $action) {
            $headerValue = $request->getHeader($key);

            if ($headerValue !== null && $headerValue !== '') {
                $result = (new Invoker())->call($action, $request);
                Response::json($result);
            }
        }

        return false;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Сопоставление маршрута
     * ---------------------------------------------------------------------------
     */

    /**
     * Проверяет, совпадает ли путь запроса с маршрутом.
     *
     * @param  Request $request
     * @param  array   $route
     * @return bool
     */
    private static function matchRoute(Request $request, array $route): bool
    {
        $path = $request->getPath();

        // Точное совпадение
        if ($path === $route['path']) {
            return true;
        }

        // Wildcard-маршрут
        if (self::isWildcardRoute($route['path'])) {
            return self::matchWildcard($route['path'], $request);
        }

        // Flexible-маршрут с параметрами {id}, {slug}
        // Сначала проверяем, содержит ли шаблон flexible-параметры
        if (!preg_match_all(self::FLEXIBLE_PARAM_REGEX, $route['path'], $matches)) {
            return false;
        }

        return self::matchFlexible($route['path'], $request);
    }

    /**
     * ---------------------------------------------------------------------------
     *  Wildcard-маршруты (/*)
     * ---------------------------------------------------------------------------
     */

    /**
     * Проверяет, является ли путь wildcard-маршрутом.
     *
     * @param  string $path
     * @return bool
     */
    private static function isWildcardRoute(string $path): bool
    {
        return str_ends_with($path, '/*');
    }

    /**
     * Сопоставляет wildcard-маршрут с путём запроса.
     *
     * @param  string  $pattern Шаблон (например, /blog/*)
     * @param  Request $request
     * @return bool
     */
    private static function matchWildcard(string $pattern, Request $request): bool
    {
        $prefix = substr($pattern, 0, -1); // /blog/
        $path   = $request->getPath();
        $base   = rtrim($prefix, '/');     // /blog

        // Точное совпадение с базой: /blog == /blog
        if ($path === $base) {
            Request::setParameter('*', '');
            return true;
        }

        // Совпадение с префиксом: /blog/post-1 начинается с /blog/
        if (str_starts_with($path, $prefix)) {
            Request::setParameter('*', substr($path, strlen($prefix)));
            return true;
        }

        return false;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Flexible-маршруты ({param})
     * ---------------------------------------------------------------------------
     */

    /**
     * Сопоставляет flexible-маршрут с путём запроса.
     *
     * @param  string  $pattern Шаблон (например, /post/{id})
     * @param  Request $request
     * @return bool
     */
    private static function matchFlexible(string $pattern, Request $request): bool
    {
        if (!preg_match_all(self::FLEXIBLE_PARAM_REGEX, $pattern, $matches)) {
            return false;
        }

        $paramNames = $matches[1]; // ['id'], ['slug'], etc.
        $regex = $pattern;

        // Заменяем {param} на регулярку
        foreach ($matches[0] as $placeholder) {
            $regex = str_replace($placeholder, self::FLEXIBLE_VALUE_REGEX, $regex);
        }

        $path = $request->getPath();

        if (preg_match("#^{$regex}$#", $path, $result)) {
            foreach ($paramNames as $index => $name) {
                Request::setParameter($name, $result[$index + 1]);
            }

            return true;
        }

        return false;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Выполнение callback
     * ---------------------------------------------------------------------------
     */

    /**
     * Выполняет все callback маршрута и возвращает массив результатов.
     *
     * @param  array   $callbacks Массив обработчиков
     * @param  Request $request
     * @return array
     */
    private static function executeCallbacks(array $callbacks, Request $request): array
    {
        $results = [];

        foreach ($callbacks as $callback) {
            $result = (new Invoker())->call($callback, $request);
            $results[] = $result;
        }

        // Фильтрация пустых результатов (null, false, '')
        return array_values(
            array_filter($results, static fn($item): bool => is_array($item) || !in_array($item, [null, false, ''], true))
        );
    }

    /**
     * ---------------------------------------------------------------------------
     *  Отправка ответа
     * ---------------------------------------------------------------------------
     */

    /**
     * Отправляет JSON-ответ с результатами выполнения маршрута.
     *
     * @param  array $results
     * @return void
     */
    private static function sendResponse(array $results): void
    {
        if ($results === []) {
            return;
        }

        if (!Header::sent()) {
            Header::json();
        }

        $data = count($results) === 1 ? $results[0] : $results;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * ---------------------------------------------------------------------------
     *  Порядок маршрутов
     * ---------------------------------------------------------------------------
     */

    /**
     * Возвращает маршруты в порядке приоритета:
     * сначала точные, потом wildcard.
     *
     * @return array
     */
    private static function getOrderedRoutes(): array
    {
        $explicit = [];
        $wildcard = [];

        foreach (self::$routes as $route) {
            if (self::isWildcardRoute($route['path'])) {
                $wildcard[] = $route;
            } else {
                $explicit[] = $route;
            }
        }

        return array_merge($explicit, $wildcard);
    }

    /**
     * ---------------------------------------------------------------------------
     *  Доступ к маршрутам (для Middleware)
     * ---------------------------------------------------------------------------
     */

    /**
     * Возвращает все зарегистрированные маршруты.
     * Используется в Middleware::set() для привязки middleware.
     *
     * @internal
     * @return array
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Возвращает количество зарегистрированных маршрутов.
     *
     * @internal
     * @return int
     */
    public static function getRouteCount(): int
    {
        return count(self::$routes);
    }

    /**
     * Устанавливает middleware для маршрута по индексу.
     *
     * @internal
     * @param  int          $index      Индекс маршрута
     * @param  mixed        $callback   Middleware callback
     * @param  bool         $isMany     Флаг множественных middleware
     * @return void
     */
    public static function setRouteMiddleware(int $index, mixed $callback, bool $isMany): void
    {
        if (!isset(self::$routes[$index])) {
            return;
        }

        self::$routes[$index]['middleware'] = $callback;

        if ($isMany) {
            self::$routes[$index]['isManyMiddle'] = true;
        }
    }
}
