<?php

namespace Pet\View;

/**
 * Blade
 *
 * Основной класс для работы с Blade-шаблонами в PET Framework.
 * Предоставляет API для рендеринга, секций, стеков и макетов,
 * аналогично Laravel Blade.
 *
 * Использование:
 * ```php
 * // Рендеринг Blade-шаблона
 * $html = Blade::render('user.profile', ['name' => 'John']);
 *
 * // Секции и макеты
 * Blade::startSection('content');
 * // ... HTML ...
 * Blade::stopSection();
 *
 * // Стеки
 * Blade::startPush('scripts');
 * // ... JS ...
 * Blade::stopPush();
 * echo Blade::renderStack('scripts');
 * ```
 */
class Blade
{
    /**
     * Директория с Blade-шаблонами.
     * По умолчанию — VIEW_DIR из конфигурации.
     */
    protected static string $viewDir = VIEW_DIR;

    /**
     * Директория кеша скомпилированных шаблонов.
     */
    protected static string $cacheDir = '';

    /**
     * Хранилище секций.
     *
     * @var array<string, string>
     */
    protected static array $sections = [];

    /**
     * Стек секций (для вложенных @section).
     *
     * @var array<int, string>
     */
    protected static array $sectionStack = [];

    /**
     * Хранилище стеков (@push / @prepend).
     *
     * @var array<string, array<int, string>>
     */
    protected static array $stacks = [];

    /**
     * Стек стеков (для вложенных @push).
     *
     * @var array<int, string>
     */
    protected static array $pushStack = [];

    /**
     * Флаг — был ли уже выполнен @extends.
     *
     * @var bool
     */
    protected static bool $extendsUsed = false;

    /**
     * Путь к лейауту, указанный в @extends.
     *
     * @var string|null
     */
    protected static ?string $layoutPath = null;

    /**
     * Зарегистрированные компоненты.
     *
     * @var array<string, string>
     */
    protected static array $components = [];

    /**
     * Зарегистрированные анонимные компоненты.
     *
     * @var array<string, string>
     */
    protected static array $anonymousComponents = [];

    /**
     * Установить директорию шаблонов.
     *
     * @param  string $dir
     * @return void
     */
    public static function setViewDir(string $dir): void
    {
        static::$viewDir = $dir;
    }

    /**
     * Установить директорию кеша.
     *
     * @param  string $dir
     * @return void
     */
    public static function setCacheDir(string $dir): void
    {
        static::$cacheDir = $dir;
    }

    /**
     * Получить директорию кеша.
     *
     * @return string
     */
    public static function getCacheDir(): string
    {
        if (static::$cacheDir === '') {
            static::$cacheDir = defined('STORAGE_DIR')
                ? STORAGE_DIR . DS . 'blade_cache'
                : sys_get_temp_dir() . DS . 'pet_blade_cache';
        }
        return static::$cacheDir;
    }

    // ================================================================
    //  РЕНДЕРИНГ
    // ================================================================

    /**
     * Рендеринг Blade-шаблона.
     *
     * @param  string $view   Имя шаблона (с точками: user.profile)
     * @param  array  $data   Параметры для шаблона
     * @param  array  $merge  Дополнительные данные для слияния
     * @return string
     */
    public static function render(string $view, array $data = [], array $merge = []): string
    {
        $data = array_merge($merge, $data);

        // Сбрасываем состояние для нового рендеринга
        static::resetState();

        $path = static::resolveViewPath($view);

        if (!$path) {
            throw new \RuntimeException("Blade view [{$view}] not found.");
        }

        // Компилируем и получаем путь к кешу
        $compiledPath = BladeCompiler::compileFile($path, $data);

        // Извлекаем переменные
        extract($data, EXTR_SKIP | EXTR_REFS);

        // Передаём $__env для доступа к секциям/стекам из шаблона
        $__env = new static();

        ob_start();
        include $compiledPath;
        $content = ob_get_clean();

        // Если был @extends — рендерим лейаут
        if (static::$extendsUsed && static::$layoutPath !== null) {
            // Сохраняем секции
            $layoutData = array_merge($data, ['__slots' => static::$sections]);
            $content = static::renderLayout(static::$layoutPath, $layoutData);
        }

        return $content;
    }

    /**
     * Рендеринг лейаута с подстановкой секций.
     *
     * @param  string $layout
     * @param  array  $data
     * @return string
     */
    protected static function renderLayout(string $layout, array $data = []): string
    {
        $path = static::resolveViewPath($layout);

        if (!$path) {
            throw new \RuntimeException("Layout view [{$layout}] not found.");
        }

        $compiledPath = BladeCompiler::compileFile($path, $data);

        extract($data, EXTR_SKIP | EXTR_REFS);
        $__env = new static();

        ob_start();
        include $compiledPath;
        return ob_get_clean();
    }

    /**
     * Разрешить путь к view-файлу.
     *
     * @param  string $view
     * @return string|null
     */
    protected static function resolveViewPath(string $view): ?string
    {
        // Заменяем точки на разделители
        $viewPath = str_replace('.', DS, $view);

        // Проверяем .blade.php
        $path = static::$viewDir . DS . $viewPath . '.blade.php';
        if (file_exists($path)) {
            return $path;
        }

        // Проверяем .php
        $path = static::$viewDir . DS . $viewPath . '.php';
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * Проверить, существует ли view-файл.
     *
     * @param  string $view
     * @return bool
     */
    public static function exists(string $view): bool
    {
        return static::resolveViewPath($view) !== null;
    }

    /**
     * Сбросить состояние перед рендерингом.
     *
     * @return void
     */
    protected static function resetState(): void
    {
        static::$sections = [];
        static::$sectionStack = [];
        static::$stacks = [];
        static::$pushStack = [];
        static::$extendsUsed = false;
        static::$layoutPath = null;
    }

    // ================================================================
    //  СЕКЦИИ (@section / @yield)
    // ================================================================

    /**
     * Начать секцию.
     *
     * @param  string      $section
     * @param  string|null $content
     * @return void
     */
    public static function startSection(string $section, ?string $content = null): void
    {
        if ($content !== null) {
            // @section('name', 'content') — inline
            if (isset(static::$sections[$section])) {
                static::$sections[$section] .= $content;
            } else {
                static::$sections[$section] = $content;
            }
        } else {
            // @section('name') ... @endsection
            ob_start();
            static::$sectionStack[] = $section;
        }
    }

    /**
     * Остановить секцию.
     *
     * @param  bool $show Показать содержимое сразу (для @show)
     * @return void
     */
    public static function stopSection(bool $show = false): void
    {
        $last = array_pop(static::$sectionStack);
        if ($last === null) {
            throw new \RuntimeException('No section started.');
        }

        $content = ob_get_clean();

        if (isset(static::$sections[$last])) {
            static::$sections[$last] .= $content;
        } else {
            static::$sections[$last] = $content;
        }

        if ($show) {
            echo $content;
        }
    }

    /**
     * Добавить секцию (для @append).
     *
     * @return void
     */
    public static function appendSection(): void
    {
        $last = array_pop(static::$sectionStack);
        if ($last === null) {
            throw new \RuntimeException('No section started.');
        }

        $content = ob_get_clean();

        if (isset(static::$sections[$last])) {
            static::$sections[$last] .= $content;
        } else {
            static::$sections[$last] = $content;
        }
    }

    /**
     * Перезаписать секцию (для @overwrite).
     *
     * @return void
     */
    public static function overwriteSection(): void
    {
        $last = array_pop(static::$sectionStack);
        if ($last === null) {
            throw new \RuntimeException('No section started.');
        }

        static::$sections[$last] = ob_get_clean();
    }

    /**
     * Получить содержимое секции (для @yield).
     *
     * @param  string $section
     * @param  string $default
     * @return string
     */
    public static function yieldContent(string $section, string $default = ''): string
    {
        return static::$sections[$section] ?? $default;
    }

    /**
     * Установить лейаут (для @extends).
     *
     * @param  string $layout
     * @return void
     */
    public static function setLayout(string $layout): void
    {
        static::$extendsUsed = true;
        static::$layoutPath = $layout;
    }

    // ================================================================
    //  СТЕКИ (@push / @prepend / @stack)
    // ================================================================

    /**
     * Начать push-стек.
     *
     * @param  string $stack
     * @return void
     */
    public static function startPush(string $stack): void
    {
        ob_start();
        static::$pushStack[] = $stack;
    }

    /**
     * Остановить push.
     *
     * @return void
     */
    public static function stopPush(): void
    {
        $last = array_pop(static::$pushStack);
        if ($last === null) {
            throw new \RuntimeException('No push stack started.');
        }

        $content = ob_get_clean();

        if (!isset(static::$stacks[$last])) {
            static::$stacks[$last] = [];
        }

        static::$stacks[$last][] = $content;
    }

    /**
     * Начать prepend-стек.
     *
     * @param  string $stack
     * @return void
     */
    public static function startPrepend(string $stack): void
    {
        ob_start();
        static::$pushStack[] = $stack;
    }

    /**
     * Остановить prepend.
     *
     * @return void
     */
    public static function stopPrepend(): void
    {
        $last = array_pop(static::$pushStack);
        if ($last === null) {
            throw new \RuntimeException('No prepend stack started.');
        }

        $content = ob_get_clean();

        if (!isset(static::$stacks[$last])) {
            static::$stacks[$last] = [];
        }

        // Вставляем в начало
        array_unshift(static::$stacks[$last], $content);
    }

    /**
     * Отрендерить стек (для @stack).
     *
     * @param  string $stack
     * @return string
     */
    public static function renderStack(string $stack): string
    {
        $contents = static::$stacks[$stack] ?? [];
        return implode("\n", $contents);
    }

    // ================================================================
    //  КОМПОНЕНТЫ
    // ================================================================

    /**
     * Зарегистрировать компонент.
     *
     * @param  string $alias
     * @param  string $view
     * @return void
     */
    public static function component(string $alias, string $view): void
    {
        static::$components[$alias] = $view;
    }

    /**
     * Зарегистрировать анонимный компонент.
     *
     * @param  string $view
     * @return void
     */
    public static function anonymousComponent(string $view): void
    {
        static::$anonymousComponents[] = $view;
    }

    /**
     * Рендеринг компонента.
     *
     * @param  string $component
     * @param  array  $data
     * @return string
     */
    public static function renderComponent(string $component, array $data = []): string
    {
        $view = static::$components[$component] ?? null;

        if ($view === null) {
            // Проверяем анонимные компоненты
            foreach (static::$anonymousComponents as $dir) {
                $path = static::$viewDir . DS . $dir . DS . $component . '.blade.php';
                if (file_exists($path)) {
                    return BladeCompiler::renderFile($path, $data);
                }
            }

            throw new \RuntimeException("Component [{$component}] not found.");
        }

        return static::render($view, $data);
    }

    // ================================================================
    //  ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ================================================================

    /**
     * Очистить кеш Blade.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        BladeCompiler::clearCache();
    }

    /**
     * Получить список всех зарегистрированных компонентов.
     *
     * @return array
     */
    public static function getComponents(): array
    {
        return static::$components;
    }

    /**
     * Получить список всех секций.
     *
     * @return array
     */
    public static function getSections(): array
    {
        return static::$sections;
    }

    /**
     * Получить список всех стеков.
     *
     * @return array
     */
    public static function getStacks(): array
    {
        return static::$stacks;
    }
}