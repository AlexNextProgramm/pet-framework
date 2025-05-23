<?php

namespace APP\Controller;

use Pet\Controller;
use Pet\Request\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        view('page.home', Request::$attribute);
    }
    public function documentation(Request $request)
    {
        view('page.documentation',Request::$attribute);
    }
}
