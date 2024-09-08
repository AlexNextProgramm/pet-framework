<?php

define("ROOT_DIR",  __DIR__);

include_once('./vendor/pet/framework/Command/Command.php');
if($argc == 1) die("Not arguments console \n");

$option = $argv;
unset($option[0]);
Command::init($option);

?>