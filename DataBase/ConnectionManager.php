<?php

namespace Pet\DataBase;

use PDO;
use PDOException;
use Pet\DataBase\Config\DataBase;
use Pet\Errors\AppException;

/**
 * Менеджер PDO-подключений к базам данных.
 * 
 * Поддерживает множество именованных подключений.
 * Ленивое создание PDO-соединений (создаются при первом запросе).
 * 
 * @package Pet\DataBase
 */
class ConnectionManager
{
    /**
     * @var array<string, PDO> Активные PDO-подключения
     */
    private static array $instances = [];

    /**
     * @var array<string, array> Конфигурации подключений (кэш)
     */
    private static array $configs = [];

    /**
     * Получает PDO-подключение по имени.
     * Если подключение ещё не создано, создаёт его.
     *
     * @param string|null $name Имя подключения (null = default)
     * @return PDO
     * @throws AppException
     */
    public static function connection(?string $name = null): PDO
    {
        $name = $name ?? DataBase::getDefault();

        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $config = DataBase::get($name);
        self::$configs[$name] = $config;

        try {
            $dsn = DataBase::dsn($name);
            $options = array_merge([
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ], $config['options'] ?? []);

            self::$instances[$name] = new PDO($dsn, $config['user'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new AppException("Connection '$name' failed: " . $e->getMessage(), (int)$e->getCode());
        }

        return self::$instances[$name];
    }

    /**
     * Проверяет, активно ли подключение с указанным именем.
     *
     * @param string|null $name
     * @return bool
     */
    public static function isConnected(?string $name = null): bool
    {
        $name = $name ?? DataBase::getDefault();
        return isset(self::$instances[$name]);
    }

    /**
     * Закрывает подключение по имени.
     *
     * @param string|null $name
     * @return void
     */
    public static function disconnect(?string $name = null): void
    {
        $name = $name ?? DataBase::getDefault();
        if (isset(self::$instances[$name])) {
            self::$instances[$name] = null;
            unset(self::$instances[$name]);
        }
    }

    /**
     * Закрывает все активные подключения.
     *
     * @return void
     */
    public static function disconnectAll(): void
    {
        foreach (array_keys(self::$instances) as $name) {
            self::disconnect($name);
        }
    }

    /**
     * Возвращает имя базы данных для подключения.
     *
     * @param string|null $name
     * @return string
     */
    public static function getDbName(?string $name = null): string
    {
        $config = DataBase::get($name);
        return $config['name'];
    }

    /**
     * Сбрасывает все подключения и конфиги (для тестов).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::disconnectAll();
        self::$configs = [];
        DataBase::reset();
    }
}
