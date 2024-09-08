<?php

use Pet\Request\Request;
use Pet\Router\Router;

Router::get('/', function(Request $request){ 
    view('home', $request->attribute);
});



?>