<?php
define('PUBLIC_DIR', __DIR__);
define('LOG_DIR', __DIR__.'/../');
define('ROOT_DIR', __DIR__.'/../');

#Настроки базы данных
define('DB_TYPE', env('DB_TYPE','mysql'));
define('DB_NAME', env('DB_NAME','petproject'));
define('DB_HOST', env('DB_HOST','localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASSWORD', env('DB_PASSWORD','2323'));
define('DB_PORT', env('DB_PORT','3306'));
?>