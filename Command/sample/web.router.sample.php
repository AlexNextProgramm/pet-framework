<?php

use Pet\Request\Request;
use Pet\Router\Router;

Router::get('/home', function(Request $request){ 
    view('home', $request->attribute);
});

Router::get('/home/{hello}', function(Request $request){ 
    view('home', $request->parametr);
});

?>