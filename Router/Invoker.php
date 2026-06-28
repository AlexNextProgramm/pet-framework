<?php

namespace Pet\Router;

use Pet\Errors\AppException;

/**
 * Invoker — динамический диспетчер вызовов.
 *
 * Принимает callback, имя класса или пару [класс, метод]
 * и вызывает его с переданными аргументами.
 *
 * Поддерживаемые форматы:
 *   - 'ClassName'              → (new ClassName())->__invoke(...$args) или new ClassName(...$args)
 *   - 'ClassName::method'      → ClassName::method(...$args)
 *   - [ClassName, 'method']    → (new ClassName())->method(...$args)
 *   - callable                 → callable(...$args)
 */
class Invoker
{
    /**
     * Вызывает класс / метод / callable с переданными аргументами.
     *
     * @param  string|array|callable $target Цель вызова
     * @param  mixed                 ...$args Аргументы, передаваемые в целевой метод
     * @return mixed Результат выполнения
     * @throws AppException
     */
    public function call(string|array|callable $target, mixed ...$args): mixed
    {
        // 1. Прямой callable (замыкание, функция, объект с __invoke)
        if (is_callable($target)) {
            return $target(...$args);
        }

        // 2. Массив [ClassName, method] или [object, method]
        if (is_array($target)) {
            return $this->callArray($target, $args);
        }

        // 3. Строка с :: (статический вызов)
        if (str_contains($target, '::')) {
            return $this->callStaticMethod($target, $args);
        }

        // 4. Имя класса — создаём экземпляр и вызываем
        return $this->callClass($target, $args);
    }

    /**
     * Вызывает метод класса из массива [ClassName, method].
     *
     * @param  array $target
     * @param  array $args
     * @return mixed
     * @throws AppException
     */
    private function callArray(array $target, array $args): mixed
    {
        $class = $target[0] ?? null;
        $method = $target[1] ?? null;

        if ($class === null) {
            throw new AppException('Invalid target array: class not specified', E_ERROR);
        }

        // Статический вызов: [ClassName::class, 'method']
        if (is_string($class) && $method !== null
            && is_callable([$class, $method], true)
            && (new \ReflectionMethod($class, $method))->isStatic()
        ) {
            return [$class, $method](...$args);
        }

        // Динамический вызов: (new ClassName())->method(...$args)
        if (is_string($class)) {
            $instance = $this->resolveClass($class);
            if ($method !== null && is_callable([$instance, $method])) {
                return $instance->$method(...$args);
            }
        }

        // Объект с методом: $object->method(...$args)
        if (is_object($class) && $method !== null && is_callable([$class, $method])) {
            return $class->$method(...$args);
        }

        throw new AppException(
            "Unable to invoke array target: " . (is_string($class) ? $class : gettype($class)) . "::{$method}",
            E_ERROR
        );
    }

    /**
     * Вызывает статический метод из строки "ClassName::method".
     *
     * @param  string $target
     * @param  array  $args
     * @return mixed
     * @throws AppException
     */
    private function callStaticMethod(string $target, array $args): mixed
    {
        $parts = explode('::', $target, 2);
        $class = $parts[0];
        $method = $parts[1];

        if (!class_exists($class, true)) {
            throw new AppException("Class not found: {$class}", E_ERROR);
        }

        if (!method_exists($class, $method)) {
            throw new AppException("Method not found: {$method} in class {$class}", E_ERROR);
        }

        return [$class, $method](...$args);
    }

    /**
     * Создаёт экземпляр класса и вызывает его.
     *
     * @param  string $class
     * @param  array  $args
     * @return mixed
     * @throws AppException
     */
    private function callClass(string $class, array $args): mixed
    {
        $instance = $this->resolveClass($class);

        // Объект с __invoke
        if (is_callable($instance)) {
            return $instance(...$args);
        }

        throw new AppException(
            "Class {$class} is not callable (no __invoke method)",
            E_ERROR
        );
    }

    /**
     * Создаёт экземпляр класса с автозагрузкой.
     *
     * @param  string $class
     * @return object
     * @throws AppException
     */
    private function resolveClass(string $class): object
    {
        if (!class_exists($class, true)) {
            throw new AppException("Class not found: {$class}", E_ERROR);
        }

        return new $class();
    }
}