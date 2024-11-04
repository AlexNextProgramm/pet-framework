<?php

namespace SPACE;

use Pet\Controller;
use Pet\Request\Request;

class NAMEController extends Controller
{
    public function index(Request $request)
    {
        view('home', $request->attribute);
    }
}
