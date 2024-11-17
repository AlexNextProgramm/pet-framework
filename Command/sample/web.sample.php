<?php

use Pet\Router\Router;
use PHP\Controller\HomeController;

Router::get('/', [HomeController::class, 'index']);
Router::get('/documentation', [HomeController::class, 'documentation']);
