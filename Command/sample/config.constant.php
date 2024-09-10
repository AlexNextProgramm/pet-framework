<?php
define('PUBLIC_DIR', __DIR__);
define('LOG_DIR', __DIR__.'/../');
define('ROOT_DIR', __DIR__.'/../');

#Настроки базы данных
defined('DB_ TYPE', env('DB_TYPE','mysql'));
defined('DB_NAME', env('DB_NAME','petproject'));
defined('DB_HOST', env('DB_HOST','localhost'));
defined('DB_USER', env('DB_USER', 'root'));
defined('DB_PASSWORD', env('DB_PASSWORD','2323'));
?>