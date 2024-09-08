<?php
use Pet\Controller;
use Pet\Request\Request;

class NAMEController extends Controller{

    function index(Request $request){
        view('home', $request->attribute);
    }
}
?>