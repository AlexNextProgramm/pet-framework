<?php

use Pet\Router\Router;
use APP\Controller\HomeController;

Router::get('/', [HomeController::class, 'index']);
Router::get('/documentation', [HomeController::class, 'documentation']);
