<?php

namespace Pet\DataBase\Config;

use Pet\Errors\AppException;

/**
 * Конфигурация подключений к базам данных.
 * 
 * Поддерживает множество именованных подключений.
 * Чтение настроек из .env с возможностью переопределения через код.
 * 
 * @package Pet\DataBase\Config
 */
class DataBase
{
    private static array $connections = [];
    private static string $defaultConnection = 'default';

    private static function loadFromEnv(): void
    {
        if (isset(self::$connections['default'])) {
            return;
        }

        self::$connections['default'] = [
            'type'     => defined('DB_TYPE') ? DB_TYPE : 'mysql',
            'host'     => defined('DB_HOST') ? DB_HOST : 'localhost',
            'port'     => defined('DB_PORT') ? DB_PORT : '3306',
            'name'     => defined('DB_NAME') ? DB_NAME : '',
            'user'     => defined('DB_USER') ? DB_USER : 'root',
            'password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            'charset'  => 'utf8mb4',
            'options'  => [],
        ];
    }

    public static function set(string $name, array $config): void
    {
        self::loadFromEnv();

        $defaults = self::$connections['default'] ?? [
            'type'     => 'mysql',
            'host'     => 'localhost',
            'port'     => '3306',
            'name'     => '',
            'user'     => 'root',
            'password' => '',
            'charset'  => 'utf8mb4',
            'options'  => [],
        ];

        self::$connections[$name] = array_merge($defaults, $config);
    }

    public static function get(?string $name = null): array
    {
        self::loadFromEnv();

        $name = $name ?? self::$defaultConnection;

        if (!isset(self::$connections[$name])) {
            throw new AppException("Database connection '$name' not configured");
        }

        return self::$connections[$name];
    }

    public static function setDefault(string $name): void
    {
        self::loadFromEnv();

        if (!isset(self::$connections[$name])) {
            throw new AppException("Cannot set default: connection '$name' not configured");
        }

        self::$defaultConnection = $name;
    }

    public static function getDefault(): string
    {
        self::loadFromEnv();
        return self::$defaultConnection;
    }

    public static function has(string $name): bool
    {
        self::loadFromEnv();
        return isset(self::$connections[$name]);
    }

    public static function dsn(?string $name = null): string
    {
        $config = self::get($name);

        $dsn = "{$config['type']}:host={$config['host']};port={$config['port']};dbname={$config['name']}";

        if (!empty($config['charset'])) {
            $dsn .= ";charset={$config['charset']}";
        }

        return $dsn;
    }

    public static function reset(): void
    {
        self::$connections = [];
        self::$defaultConnection = 'default';
    }
}
