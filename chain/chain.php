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
?>