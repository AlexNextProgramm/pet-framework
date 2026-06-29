<?php

namespace Pet\View;

/**
 * BladeCompiler
 *
 * Компилирует Blade-синтаксис (.blade.php) в чистый PHP-код.
 * Поддерживает: {{ }}, {!! !!}, @if, @elseif, @else, @endif,
 * @foreach, @endforeach, @for, @endfor, @while, @endwhile,
 * @isset, @endisset, @empty, @endempty, @php, @endphp,
 * @include, @extends, @section, @yield, @csrf, @method,
 * @error, @json, @verbatim, @endverbatim, @each, @forelse,
 * @empty (forelse), @endforelse, @switch, @case, @break, @default, @endswitch,
 * @auth, @endauth, @guest, @endguest, @production, @endproduction,
 * @env, @endenv, @push, @endpush, @stack, @prepend, @endprepend,
 * @once, @endonce, @class, @style, @checked, @selected, @disabled,
 * @readonly, @required, @props, @aware, @use, @js, @css, @vite,
 * @includeIf, @includeWhen, @includeUnless, @includeFirst,
 * @each (упрощённый), @dd, @dump, @debug
 */
class BladeCompiler
{
    /**
     * Зарегистрированные пользовательские директивы.
     *
     * @var array<string, callable>
     */
    protected static array $customDirectives = [];

    /**
     * Зарегистрированные условные директивы.
     *
     * @var array<string, callable>
     */
    protected static array $customConditions = [];

    /**
     * Стек для @push / @prepend.
     *
     * @var array<string, array<int, string>>
     */
    protected static array $stacks = [];

    /**
     * Стек для @once.
     *
     * @var array<int, string>
     */
    protected static array $onceStack = [];

    /**
     * Зарегистрировать пользовательскую директиву.
     *
     * @param  string   $name
     * @param  callable $callback
     * @return void
     */
    public static function directive(string $name, callable $callback): void
    {
        static::$customDirectives[$name] = $callback;
    }

    /**
     * Зарегистрировать пользовательское условие.
     *
     * @param  string   $name
     * @param  callable $callback
     * @return void
     */
    public static function if(string $name, callable $callback): void
    {
        static::$customConditions[$name] = $callback;
    }

    /**
     * Скомпилировать Blade-шаблон в PHP.
     *
     * @param  string $content Сырой контент .blade.php файла
     * @return string PHP-код
     */
    public static function compile(string $content): string
    {
        $content = static::compileComments($content);
        $content = static::compileVerbatim($content);
        $content = static::compileEscapedEchos($content);
        $content = static::compileRawEchos($content);
        $content = static::compileRegularEchos($content);
        $content = static::compileStructure($content);
        $content = static::compileIncludes($content);
        $content = static::compileLayouts($content);
        $content = static::compileCustomDirectives($content);
        $content = static::compilePhp($content);
        $content = static::compileStacks($content);
        $content = static::compileStrings($content);

        return $content;
    }

    /**
     * Скомпилировать файл и вернуть путь к кешу.
     *
     * @param  string $path    Путь к .blade.php файлу
     * @param  array  $params  Параметры для шаблона
     * @return string Путь к скомпилированному PHP-файлу
     */
    public static function compileFile(string $path, array $params = []): string
    {
        $content = file_get_contents($path);
        $compiled = static::compile($content);

        // Директория кеша
        $cacheDir = defined('STORAGE_DIR')
            ? STORAGE_DIR . DS . 'blade_cache'
            : sys_get_temp_dir() . DS . 'pet_blade_cache';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Хеш от пути и содержимого
        $hash = md5($path . filemtime($path));
        $cachedPath = $cacheDir . DS . $hash . '.php';

        // Если кеш устарел — перезаписываем
        if (!file_exists($cachedPath) || filemtime($path) > filemtime($cachedPath)) {
            file_put_contents($cachedPath, $compiled);
        }

        return $cachedPath;
    }

    /**
     * Скомпилировать и выполнить Blade-шаблон, вернуть HTML.
     *
     * @param  string $path   Путь к .blade.php файлу
     * @param  array  $params Параметры для шаблона
     * @return string
     */
    public static function renderFile(string $path, array $params = []): string
    {
        $cachedPath = static::compileFile($path, $params);

        extract($params, EXTR_SKIP | EXTR_REFS);

        ob_start();
        include $cachedPath;
        return ob_get_clean();
    }

    // ================================================================
    //  КОМПИЛЯЦИЯ
    // ================================================================

    /**
     * Компиляция @verbatim ... @endverbatim
     */
    protected static function compileVerbatim(string $content): string
    {
        $pattern = '/@verbatim\s*([\s\S]*?)\s*@endverbatim/m';
        return preg_replace_callback($pattern, function ($matches) {
            // Экранируем содержимое, чтобы оно не компилировалось
            return '<?php echo \'' . str_replace(
                ["'", '{{', '{!!'],
                ["\\'", '{{', '{!!'],
                $matches[1]
            ) . '\'; ?>';
        }, $content);
    }

    /**
     * Компиляция {{-- комментарий --}}
     * Полностью удаляет Blade-комментарии из выходного HTML.
     */
    protected static function compileComments(string $content): string
    {
        $pattern = '/\{\{--[\s\S]*?--\}\}/m';
        return preg_replace($pattern, '', $content);
    }

    /**
     * Компиляция {!! $var !!} (без экранирования)
     */
    protected static function compileRawEchos(string $content): string
    {
        $pattern = '/\{!!([\s\S]*?)!!\}/m';
        return preg_replace($pattern, '<?php echo $1; ?>', $content);
    }

    /**
     * Компиляция {{ $var }} (с экранированием)
     */
    protected static function compileRegularEchos(string $content): string
    {
        $pattern = '/\{\{([\s\S]*?)\}\}/m';
        return preg_replace($pattern, '<?php echo htmlspecialchars($1 ?? \'\', ENT_QUOTES | ENT_HTML5, \'UTF-8\'); ?>', $content);
    }

    /**
     * Компиляция {{{ $var }}} (экранирование с ENT_QUOTES)
     */
    protected static function compileEscapedEchos(string $content): string
    {
        $pattern = '/\{\{\{([\s\S]*?)\}\}\}/m';
        return preg_replace($pattern, '<?php echo htmlspecialchars($1 ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>', $content);
    }

    /**
     * Компиляция структурных директив: @if, @foreach, @section и т.д.
     */
    protected static function compileStructure(string $content): string
    {
        $directives = [
            // Условные
            '/@unless\s*\(([\s\S]*?)\)\s*$/m' => '<?php if (!($1)): ?>',
            '/@endunless\s*$/m' => '<?php endif; ?>',
            '/@isset\s*\(([\s\S]*?)\)\s*$/m' => '<?php if (isset($1)): ?>',
            '/@endisset\s*$/m' => '<?php endif; ?>',
            '/@empty\s*\(([\s\S]*?)\)\s*$/m' => '<?php if (empty($1)): ?>',
            '/@endempty\s*$/m' => '<?php endif; ?>',
            '/@auth\s*$/m' => '<?php if (auth()): ?>',
            '/@endauth\s*$/m' => '<?php endif; ?>',
            '/@guest\s*$/m' => '<?php if (!auth()): ?>',
            '/@endguest\s*$/m' => '<?php endif; ?>',
            '/@production\s*$/m' => '<?php if (env(\'APP_ENV\') === \'production\'): ?>',
            '/@endproduction\s*$/m' => '<?php endif; ?>',
            '/@env\s*\(([\s\S]*?)\)\s*$/m' => '<?php if (env(\'APP_ENV\') === $1): ?>',
            '/@endenv\s*$/m' => '<?php endif; ?>',

            // Switch
            '/@switch\s*\(([\s\S]*?)\)\s*$/m' => '<?php switch($1): ?>',
            '/@case\s*\(([\s\S]*?)\)\s*$/m' => '<?php case $1: ?>',
            '/@break\s*$/m' => '<?php break; ?>',
            '/@default\s*$/m' => '<?php default: ?>',
            '/@endswitch\s*$/m' => '<?php endswitch; ?>',

            // Циклы
            '/@forelse\s*\(([\s\S]*?)\s+as\s+([\s\S]*?)\)\s*$/m' => '<?php foreach($1): ?>',
            '/@empty\s*$/m' => '<?php endforeach; ?><?php if (empty($1)): ?>',
            '/@endforelse\s*$/m' => '<?php endif; ?>',
            '/@each\s*\(([\s\S]*?)\)\s*$/m' => '<?php foreach($1): ?>',

            // @once
            '/@once\s*$/m' => '<?php if (!isset($__onceStack[\'' . uniqid() . '\'])): ?>',
            '/@endonce\s*$/m' => '<?php endif; ?>',

            // @class
            '/@class\s*\(([\s\S]*?)\)\s*$/m' => 'class="<?php echo htmlspecialchars(implode(\' \', array_keys(array_filter($1))), ENT_QUOTES | ENT_HTML5, \'UTF-8\'); ?>"',

            // @style
            '/@style\s*\(([\s\S]*?)\)\s*$/m' => 'style="<?php echo htmlspecialchars(implode(\';\', array_map(fn($k, $v) => \'$k: $v\', array_keys($1), $1)), ENT_QUOTES | ENT_HTML5, \'UTF-8\'); ?>"',

            // Атрибуты форм
            '/@checked\s*\(([\s\S]*?)\)\s*$/m' => '<?php if ($1): ?>checked<?php endif; ?>',
            '/@selected\s*\(([\s\S]*?)\)\s*$/m' => '<?php if ($1): ?>selected<?php endif; ?>',
            '/@disabled\s*\(([\s\S]*?)\)\s*$/m' => '<?php if ($1): ?>disabled<?php endif; ?>',
            '/@readonly\s*\(([\s\S]*?)\)\s*$/m' => '<?php if ($1): ?>readonly<?php endif; ?>',
            '/@required\s*\(([\s\S]*?)\)\s*$/m' => '<?php if ($1): ?>required<?php endif; ?>',

            // @dd, @dump
            '/@dd\s*\(([\s\S]*?)\)\s*$/m' => '<?php dd($1); ?>',
            '/@dump\s*\(([\s\S]*?)\)\s*$/m' => '<?php var_dump($1); ?>',
            '/@debug\s*$/m' => '<?php if (defined(\'APP_DEBUG\') && APP_DEBUG): ?>',
            '/@enddebug\s*$/m' => '<?php endif; ?>',
        ];

        foreach ($directives as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        // @if, @elseif, @else, @endif
        $content = preg_replace(
            '/@if\s*\(([\s\S]*?)\)\s*$/m',
            '<?php if ($1): ?>',
            $content
        );
        $content = preg_replace(
            '/@elseif\s*\(([\s\S]*?)\)\s*$/m',
            '<?php elseif ($1): ?>',
            $content
        );
        $content = preg_replace('/@else\s*$/m', '<?php else: ?>', $content);
        $content = preg_replace('/@endif\s*$/m', '<?php endif; ?>', $content);

        // @for, @endfor
        $content = preg_replace(
            '/@for\s*\(([\s\S]*?)\)\s*$/m',
            '<?php for ($1): ?>',
            $content
        );
        $content = preg_replace('/@endfor\s*$/m', '<?php endfor; ?>', $content);

        // @foreach, @endforeach
        $content = preg_replace(
            '/@foreach\s*\(([\s\S]*?)\)\s*$/m',
            '<?php foreach ($1): ?>',
            $content
        );
        $content = preg_replace('/@endforeach\s*$/m', '<?php endforeach; ?>', $content);

        // @while, @endwhile
        $content = preg_replace(
            '/@while\s*\(([\s\S]*?)\)\s*$/m',
            '<?php while ($1): ?>',
            $content
        );
        $content = preg_replace('/@endwhile\s*$/m', '<?php endwhile; ?>', $content);

        // @continue, @break с опциональным условием
        $content = preg_replace(
            '/@continue\s*\(([\s\S]*?)\)\s*$/m',
            '<?php if ($1) continue; ?>',
            $content
        );
        $content = preg_replace('/@continue\s*$/m', '<?php continue; ?>', $content);
        $content = preg_replace(
            '/@break\s*\(([\s\S]*?)\)\s*$/m',
            '<?php if ($1) break; ?>',
            $content
        );

        return $content;
    }

    /**
     * Компиляция @include, @includeIf, @includeWhen, @includeUnless, @includeFirst.
     */
    protected static function compileIncludes(string $content): string
    {
        // @includeIf('view')
        $content = preg_replace(
            '/@includeIf\s*\(([\s\S]*?)\)\s*$/m',
            '<?php if (Pet\\View\\View::exists($1)): ?>' . "\n" . '<?php Pet\\View\\View::open($1, array_merge(get_defined_vars(), $__data ?? [])); ?>' . "\n" . '<?php endif; ?>',
            $content
        );

        // @includeWhen(condition, 'view')
        $content = preg_replace(
            '/@includeWhen\s*\(([\s\S]*?),\s*([\s\S]*?)\)\s*$/m',
            '<?php if ($1): ?>' . "\n" . '<?php Pet\\View\\View::open($2, array_merge(get_defined_vars(), $__data ?? [])); ?>' . "\n" . '<?php endif; ?>',
            $content
        );

        // @includeUnless(condition, 'view')
        $content = preg_replace(
            '/@includeUnless\s*\(([\s\S]*?),\s*([\s\S]*?)\)\s*$/m',
            '<?php if (!($1)): ?>' . "\n" . '<?php Pet\\View\\View::open($2, array_merge(get_defined_vars(), $__data ?? [])); ?>' . "\n" . '<?php endif; ?>',
            $content
        );

        // @includeFirst(['view1', 'view2'])
        $content = preg_replace(
            '/@includeFirst\s*\(([\s\S]*?)\)\s*$/m',
            '<?php foreach ((array)$1 as \$__view): ?>' . "\n" . '<?php if (Pet\\View\\View::exists(\$__view)): ?>' . "\n" . '<?php Pet\\View\\View::open(\$__view, array_merge(get_defined_vars(), $__data ?? [])); ?>' . "\n" . '<?php break; ?>' . "\n" . '<?php endif; ?>' . "\n" . '<?php endforeach; ?>',
            $content
        );

        // @include('view', ['key' => 'value'])
        $content = preg_replace(
            '/@include\s*\(([\s\S]*?)\)\s*$/m',
            '<?php Pet\\View\\View::open($1, array_merge(get_defined_vars(), $__data ?? [])); ?>',
            $content
        );

        return $content;
    }

    /**
     * Компиляция @extends, @section, @yield, @props, @aware.
     */
    protected static function compileLayouts(string $content): string
    {
        // @props(['type' => 'default', 'message' => ''])
        $content = preg_replace(
            '/@props\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__props = $1; foreach ($__props as $__propKey => $__propDefault): ?>' . "\n" . '<?php if (!isset($$__propKey)) $$__propKey = $__propDefault; ?>' . "\n" . '<?php endforeach; ?>',
            $content
        );

        // @aware(['type' => 'default'])
        $content = preg_replace(
            '/@aware\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__aware = $1; foreach ($__aware as $__awareKey => $__awareDefault): ?>' . "\n" . '<?php if (!isset($$__awareKey)) $$__awareKey = $__awareDefault; ?>' . "\n" . '<?php endforeach; ?>',
            $content
        );

        // @extends('layout')
        $content = preg_replace(
            '/@extends\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__layout = $1; ?>',
            $content
        );

        // @section('name', 'content') — inline
        $content = preg_replace(
            '/@section\s*\(([\s\S]*?),\s*([\s\S]*?)\)\s*$/m',
            '<?php $__env->startSection($1, $2); ?>',
            $content
        );

        // @section('name') ... @endsection / @stop
        $content = preg_replace(
            '/@section\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__env->startSection($1); ?>',
            $content
        );
        $content = preg_replace('/@(endsection|stop)\s*$/m', '<?php $__env->stopSection(); ?>', $content);

        // @yield('name') или @yield('name', 'default')
        $content = preg_replace(
            '/@yield\s*\(([^)]*)\)/m',
            '<?php echo $__env->yieldContent($1); ?>',
            $content
        );

        // @show
        $content = preg_replace('/@show\s*$/m', '<?php $__env->stopSection(true); ?>', $content);

        // @append
        $content = preg_replace('/@append\s*$/m', '<?php $__env->appendSection(); ?>', $content);

        // @overwrite
        $content = preg_replace('/@overwrite\s*$/m', '<?php $__env->overwriteSection(); ?>', $content);

        return $content;
    }

    /**
     * Компиляция @php ... @endphp.
     */
    protected static function compilePhp(string $content): string
    {
        // @php(expression) — однострочный
        $content = preg_replace(
            '/@php\s*\(([\s\S]*?)\)\s*$/m',
            '<?php ($1); ?>',
            $content
        );

        // @php ... @endphp — многострочный
        $content = preg_replace(
            '/@php\s*$/m',
            '<?php ',
            $content
        );
        $content = preg_replace('/@endphp\s*$/m', '?>', $content);

        return $content;
    }

    /**
     * Компиляция @push, @prepend, @stack.
     */
    protected static function compileStacks(string $content): string
    {
        // @push('name')
        $content = preg_replace(
            '/@push\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__env->startPush($1); ?>',
            $content
        );
        $content = preg_replace('/@endpush\s*$/m', '<?php $__env->stopPush(); ?>', $content);

        // @prepend('name')
        $content = preg_replace(
            '/@prepend\s*\(([\s\S]*?)\)\s*$/m',
            '<?php $__env->startPrepend($1); ?>',
            $content
        );
        $content = preg_replace('/@endprepend\s*$/m', '<?php $__env->stopPrepend(); ?>', $content);

        // @stack('name')
        $content = preg_replace(
            '/@stack\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo $__env->renderStack($1); ?>',
            $content
        );

        return $content;
    }

    /**
     * Компиляция @csrf, @method, @json, @js, @css.
     */
    protected static function compileStrings(string $content): string
    {
        // @csrf
        $content = preg_replace('/@csrf\s*$/m', '<?php echo \'<input type="hidden" name="_token" value="\' . htmlspecialchars(csrf_token() ?? \'\', ENT_QUOTES | ENT_HTML5, \'UTF-8\') . \'">\'; ?>', $content);

        // @method('PUT')
        $content = preg_replace(
            '/@method\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo \'<input type="hidden" name="_method" value="\' . htmlspecialchars($1, ENT_QUOTES | ENT_HTML5, \'UTF-8\') . \'">\'; ?>',
            $content
        );

        // @json($var)
        $content = preg_replace(
            '/@json\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo json_encode($1, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>',
            $content
        );

        // @error('field')
        $content = preg_replace(
            '/@error\s*\(([\s\S]*?)\)\s*$/m',
            '<?php if (isset($errors) && $errors->has($1)): ?>',
            $content
        );
        $content = preg_replace('/@enderror\s*$/m', '<?php endif; ?>', $content);

        // @js($var) — для инерции
        $content = preg_replace(
            '/@js\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo \'<script>window.__PET_DATA__ = \' . json_encode($1, JSON_UNESCAPED_UNICODE) . \'</script>\'; ?>',
            $content
        );

        // @css('path')
        $content = preg_replace(
            '/@css\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo \'<link rel="stylesheet" href="\' . htmlspecialchars($1, ENT_QUOTES | ENT_HTML5, \'UTF-8\') . \'">\'; ?>',
            $content
        );

        // @vite('resources/js/app.js')
        $content = preg_replace(
            '/@vite\s*\(([\s\S]*?)\)\s*$/m',
            '<?php echo \'<script type="module" src="\' . htmlspecialchars($1, ENT_QUOTES | ENT_HTML5, \'UTF-8\') . \'"></script>\'; ?>',
            $content
        );

        // @use(Iterator::class)
        $content = preg_replace(
            '/@use\s*\(([\s\S]*?)\)\s*$/m',
            '<?php use $1; ?>',
            $content
        );

        return $content;
    }

    /**
     * Компиляция пользовательских директив.
     */
    protected static function compileCustomDirectives(string $content): string
    {
        foreach (static::$customDirectives as $name => $callback) {
            $pattern = '/@' . preg_quote($name, '/') . '\s*\(([\s\S]*?)\)\s*$/m';
            $content = preg_replace_callback($pattern, function ($matches) use ($callback) {
                $expression = $matches[1] ?? '';
                $php = $callback($expression);
                return '<?php ' . $php . '; ?>';
            }, $content);
        }

        // Пользовательские условия @custom(...) / @endcustom
        foreach (static::$customConditions as $name => $callback) {
            $pattern = '/@' . preg_quote($name, '/') . '\s*\(([\s\S]*?)\)\s*$/m';
            $content = preg_replace_callback($pattern, function ($matches) use ($name) {
                $expression = $matches[1] ?? '';
                return '<?php if (Pet\\View\\BladeCompiler::checkCondition(\'' . $name . '\', ' . $expression . ')): ?>';
            }, $content);

            $content = preg_replace(
                '/@end' . preg_quote($name, '/') . '\s*$/m',
                '<?php endif; ?>',
                $content
            );
        }

        return $content;
    }

    /**
     * Проверить пользовательское условие.
     *
     * @param  string $name
     * @param  mixed  ...$args
     * @return bool
     */
    public static function checkCondition(string $name, ...$args): bool
    {
        if (isset(static::$customConditions[$name])) {
            return (bool) call_user_func_array(static::$customConditions[$name], $args);
        }
        return false;
    }

    /**
     * Получить содержимое стека.
     *
     * @param  string $name
     * @return array
     */
    public static function getStack(string $name): array
    {
        return static::$stacks[$name] ?? [];
    }

    /**
     * Очистить кеш скомпилированных Blade-шаблонов.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $cacheDir = defined('STORAGE_DIR')
            ? STORAGE_DIR . DS . 'blade_cache'
            : sys_get_temp_dir() . DS . 'pet_blade_cache';

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . DS . '*.php');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}