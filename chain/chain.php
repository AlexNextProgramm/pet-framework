<?php

use Pet\Request\Request;
use Pet\View\View;

function view($name, $argument)
{
    (new View())->open($name, $argument);
}
function attr($name = null)
{
    return $GLOBALS['app']->request->input($name);
}

function request(): Request
{
    return $GLOBALS['app']->request;
}

function files(string $name = null)
{
    return request()->file($name);
}

/**
 * supple
 * гибкая ссылка url/{name}
 * @param  string $key
 * @return null|array|string
 */
function supple(string $key = null):null|array|string
{
    $parametr = request()->parametr;
    if($key) {
        return !empty($parametr[$key]) ? $parametr[$key] : null;
    }
    return $parametr;
}
?>