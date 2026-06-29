<?php

use Pet\File\File;
use Pet\File\FileCollection;
use Pet\Request\Request;
use Pet\Territory;
use Pet\View\View;
use Pet\View\Blade;

/**
 * view
 *
 * @param  string $name
 * @param  array $argument
 * @return void
 */
function view(string $name, array $argument = [])
{
     View::open($name, $argument);
}

/**
 * attr
 * @param  string|null $name
 * @return string
 */
function attr(string|null $name = null): string|array|null
{
    return request()->input($name);
}
/**
 * attrs
  * @return array
 */

function attrs(): array
{
    return Request::$attribute;
}

/**
 * request
 * @return Request
 */
function request(): Request
{
    return $GLOBALS['app']->request;
}

/**
 * files
 * Возвращает загруженный файл или коллекцию файлов.
 *
 * @param  string|null $name Имя поля в $_FILES
 * @return File|FileCollection|array|null
 */
function files(string|null $name = null): File|FileCollection|array|null
{
    return request()->file($name);
}

/**
 * supple
 * гибкая ссылка url/{name}
 * @param  string $key
 * @return null|array|string
 */
function supple(string|null $key = null):null|array|string
{
    $parametr = Request::$parametr;
    if($key) {
        return !empty($parametr[$key]) ? $parametr[$key] : null;
    }
    return $parametr;
}

/**
 * levels
 * сегменты пути после домена /a/b/c -> ['a', 'b', 'c']
 * @return array
 */
function levels(): array
{
    return Request::$levels;
}

/**
 * original
 * доменное имя запроса
 * @return string
 */
function original(): string
{
    return Request::$original;
}

/**
 * blade
 *
 * Рендеринг Blade-шаблона с возвратом HTML.
 * Аналог view(), но для .blade.php шаблонов.
 *
 * @param  string $name Имя шаблона (с точками: user.profile)
 * @param  array  $data Параметры для шаблона
 * @return string HTML
 */
function blade(string $name, array $data = []): string
{
    return Blade::render($name, $data);
}

?>