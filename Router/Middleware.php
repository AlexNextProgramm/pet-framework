<?php

namespace Pet\Router;

use Pet\Router\Router;

/**
 * Middleware — абстрактный класс для привязки middleware к маршрутам.
 *
 * Позволяет зарегистрировать callback (или массив callbacks) через middleware()
 * и применить его к одному или нескольким маршрутам через set().
 *
 * @see Router
 */
abstract class Middleware
{
    /**
     * ID первого маршрута, с которого начинается применение middleware.
     */
    private static int $startRouteId = 0;

    /**
     * Зарегистрированный middleware callback (или массив callbacks).
     *
     * @var callable|array<callable>|null
     */
    private static mixed $callback = null;

    /**
     * Флаг: передан массив middleware (множественный режим).
     */
    private static bool $isMany = false;

    /**
     * Регистрирует middleware для последующих маршрутов.
     *
     * Принимает один или несколько callable/классов.
     * Возвращает новый экземпляр Router для построения цепочки.
     *
     * @param  callable|string|array ...$callback Один или несколько middleware
     * @return Router
     */
    public static function middleware(callable|string|array ...$callback): Router
    {
        self::$isMany = count($callback) > 1;
        self::$callback = self::$isMany ? $callback : $callback[0];
        self::$startRouteId = Router::getRouteCount();

        return new Router();
    }

    /**
     * Применяет зарегистрированный middleware к указанным маршрутам.
     *
     * Принимает один или несколько объектов Router (результат вызова get/post/...).
     * Для каждого маршрута в диапазоне от startRouteId до конца устанавливается middleware.
     *
     * @param  Router ...$routes Объекты маршрутов для привязки middleware
     * @return void
     */
    public static function set(Router ...$routes): void
    {
        if (self::$callback === null) {
            return;
        }

        $routeCount = count($routes) + self::$startRouteId;

        for ($i = self::$startRouteId; $i < $routeCount; $i++) {
            Router::setRouteMiddleware($i, self::$callback, self::$isMany);
        }
    }
}
