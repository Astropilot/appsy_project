<?php

error_reporting(-1);

include_once '../app/autoload.php';

use Testify\Router\Request;
use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\Database;
use Testify\Component\I18n;
use Testify\Config;

session_start();

Database::getInstance(new Config);

I18n::getInstance('src/langs/', 'en');
Router::getInstance(new Request);

include_once '../src/views/Views.php';
include_once '../src/controllers/User.php';
include_once '../src/controllers/Faq.php';
include_once '../src/controllers/Message.php';
include_once '../src/controllers/Forum.php';

$router->setNoRouteHandler(function($request) {
    return Response::fromView('404.html');
});
