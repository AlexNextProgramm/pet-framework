<?php

namespace Pet\View;

use Pet\Debug\DebugBar;
use Pet\Errors\AppException;

class View
{
    const DIR_VIEW = VIEW_DIR;
    private static $argument = [];

    /**
     * @var bool Флаг: DebugBar уже вставлен в итоговый HTML
     */
    private static bool $debugBarInjected = false;

    /**
     * open
     *
     * Открывает и отображает view-файл.
     * Поддерживает как обычные .php, так и .blade.php шаблоны.
     * Для Blade-шаблонов использует BladeCompiler и Blade.
     *
     * @param  string $viewName Имя шаблона (с точками: user.profile)
     * @param  array  $argument Параметры для шаблона
     * @return void
     */
    public static function open(string $viewName, array $argument = []): void {
        if (!is_dir(self::DIR_VIEW)) {
            throw new AppException("not directory view", E_ERROR);
        }

        // Определяем путь к файлу
        $viewPath = implode(DS, explode(".", $viewName));

        // Проверяем .blade.php
        $bladePath = self::DIR_VIEW . DS . $viewPath . '.blade.php';
        if (file_exists($bladePath)) {
            // Рендерим Blade-шаблон
            $html = Blade::render($viewName, $argument);
            echo self::injectDebugBar($html);
            return;
        }

        // Проверяем .php
        $phpPath = self::DIR_VIEW . DS . $viewPath . '.php';
        if (!file_exists($phpPath)) {
            throw new AppException("Not file in class view: " . $phpPath, E_ERROR);
        }

        self::$argument = array_merge(self::$argument, $argument);
        foreach (self::$argument as $key => $val) {
            if (isset(${$key})) {
                throw new AppException("You are trying to redefine a variable $key");
            }
            ${$key} = $val;
        }

        // Буферизуем вывод для инъекции DebugBar
        ob_start();
        include $phpPath;
        $html = ob_get_clean();
        echo self::injectDebugBar($html);
    }

    /**
     * Проверить, существует ли view-файл.
     *
     * @param  string $viewName Имя шаблона (с точками)
     * @return bool
     */
    public static function exists(string $viewName): bool
    {
        $viewPath = implode(DS, explode(".", $viewName));

        // Проверяем .blade.php
        if (file_exists(self::DIR_VIEW . DS . $viewPath . '.blade.php')) {
            return true;
        }

        // Проверяем .php
        if (file_exists(self::DIR_VIEW . DS . $viewPath . '.php')) {
            return true;
        }

        return false;
    }

    public static function append(array $data){
        self::$argument = array_merge(self::$argument, $data);
    }

    /**
     * appendHtmlspecialchars
     *
     * Добавляет данные в $argument, предварительно применяя
     * htmlspecialchars ко всем строковым значениям массива.
     * Рекурсивно обрабатывает вложенные массивы.
     *
     * @param  array $data
     * @return void
     */
    public static function appendHtmlspecialchars(array $data): void
    {
        $escapedData = self::escapeArray($data);
        self::$argument = array_merge(self::$argument, $escapedData);
    }

    /**
     * escapeArray
     *
     * Рекурсивно применяет htmlspecialchars ко всем строковым
     * значениям в массиве.
     *
     * @param  array $data
     * @return array
     */
    private static function escapeArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::escapeArray($value);
            } elseif (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $result[$key] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } else {
                    $result[$key] = $value;
                }
            } elseif (is_string($value)) {
                $result[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * getPath
     *
     * Преобразует имя с точками в путь с разделителями.
     *
     * @param  string $path Имя с точками
     * @param  string $exp  Расширение файла
     * @return string
     */
    public static function gp(string $path, string $exp = ".php"): string
    {
        return str_replace(".", DS, $path)."$exp";
    }

    /**
     * getTemplate
     *
     * Возвращает HTML шаблона как строку.
     * Поддерживает как .php, так и .blade.php шаблоны.
     *
     * @param  string $filename Имя шаблона (с точками)
     * @param  array  $params   Параметры для шаблона
     * @return string|false
     */
    public static function getTemplate($filename, $params = [])
    {
        $viewPath = str_replace(".", DS, $filename);

        // Проверяем .blade.php
        $bladePath = self::DIR_VIEW . DS . $viewPath . '.blade.php';
        if (file_exists($bladePath)) {
            $html = Blade::render($filename, $params);
            return self::injectDebugBar($html);
        }

        // Проверяем .php
        $templatePath = self::DIR_VIEW . DS . $viewPath . '.php';
        if (is_file($templatePath)) {
            ob_start();
            if (!empty($params)) {
                extract($params, EXTR_SKIP | EXTR_REFS);
            }
            include $templatePath;
            $html = ob_get_clean();
            return self::injectDebugBar($html);
        }

        return false;
    }

    /**
     * injectDebugBar
     *
     * Вставляет отладочную панель DebugBar перед </body>,
     * если включён режим отладки (PET_DEBUG).
     *
     * @param  string $html HTML-контент
     * @return string
     */
    private static function injectDebugBar(string $html): string
    {
        if (!defined('PET_DEBUG') || PET_DEBUG !== true || self::$debugBarInjected) {
            return $html;
        }

        // Вставляем панель только в полный HTML-документ (init.php и т.п.),
        // а не в частичные шаблоны вроде layout.php без </body>.
        $pos = strripos($html, '</body>');
        if ($pos === false) {
            return $html;
        }

        self::$debugBarInjected = true;

        // Останавливаем сбор данных и получаем HTML панели
        DebugBar::stop();
        $debugHtml = DebugBar::render();

        return substr_replace($html, $debugHtml . "\n", $pos, 0);
    }
}