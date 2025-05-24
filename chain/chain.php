<?php

use Pet\Request\Request;
use Pet\Territory;
use Pet\View\View;

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
 * получает файл
 * @param  string $name
 * @return void
 */
function files(string|null $name = null): array|string|null
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

?>