<?php

#Константы Папок
define('ROOT_DIR', __DIR__ );
define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', ROOT_DIR . DS . env('PUBLIC_DIR', 'dist'));
// define('LOG_DIR', __DIR__ . '/');

#Настроки базы данных
setConstantEnv(ROOT_DIR);