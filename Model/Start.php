<?php
namespace Pet\Model;

class Start {

    public $sampleDir = "";
   public function __construct()
    {
        $this->$sampleDir = realpath(__DIR__."/../Command/sample/");
    }
    function init($name)
    {
        
    }
}