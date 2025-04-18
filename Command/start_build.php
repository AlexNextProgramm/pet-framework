<?php
include_once(__DIR__ . "/Build.php");
include_once(__DIR__. "/../function.php");
use Pet\Command\Build;
(new Build())->architecture();