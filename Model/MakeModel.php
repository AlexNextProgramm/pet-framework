<?php

namespace Pet\Model;

use Pet\View\BladeCompiler;

/**
 * MakeModel
 *
 * Генерирует класс модели на основе Blade-шаблона stub/Model.blade.php.
 * Использует BladeCompiler для компиляции шаблона в PHP-код.
 *
 * Использование:
 * ```php
 * new MakeModel('User');
 * // Создаёт app/Model/User.php из stub/Model.blade.php
 * ```
 */
class MakeModel
{
    /**
     * Директория, куда сохранять модель (app/Model).
     */
    protected static string $DIR = '';

    /**
     * Имя папки для моделей.
     */
    protected static string $nameFolder = 'Model';

    /**
     * Путь к Blade-шаблону модели.
     */
    protected static string $stubPath = '';

    /**
     * @param string $name Имя модели (например, User)
     */
    public function __construct(string $name)
    {
        // Инициализация путей
        static::$DIR = ROOT . DS . PUBLIC_DIR . DS . APP;
        static::$stubPath = __DIR__ . '/../blade/Model.blade.php';

        if (!is_dir(static::$DIR . DS . static::$nameFolder)) {
            mkdir(static::$DIR . DS . static::$nameFolder, 0777, true);
        }

        $name = ucfirst($name);
        $this->createModel($name);
    }

    /**
     * Создать файл модели из Blade-шаблона.
     *
     * @param  string $name Имя класса модели
     * @return void
     */
    private function createModel(string $name): void
    {
        // Данные для шаблона
        $data = [
            'namespace'  => 'App\\' . static::$nameFolder,
            'className'  => $name,
            'table'      => $this->tableName($name),
            'fillable'   => [],
            'hidden'     => ['password'],
            'casts'      => [],
            'timestamps' => true,
            'connection' => null,
        ];

        // Компилируем Blade-шаблон в PHP
        $compiled = BladeCompiler::renderFile(static::$stubPath, $data);

        // Сохраняем в файл
        $folder = static::$DIR . DS . static::$nameFolder . DS . "$name.php";
        file_put_contents($folder, $compiled);
    }

    /**
     * Преобразовать имя класса в имя таблицы (CamelCase -> snake_case + s).
     *
     * @param  string $name
     * @return string
     */
    private function tableName(string $name): string
    {
        // User -> users, Post -> posts, UserRole -> user_roles
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        return $snake . 's';
    }
}
