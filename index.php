<?php

namespace Testify;

error_reporting(-1);

include_once 'utils/autoload.php';

use Testify\Utils\Router\Request;
use Testify\Utils\Router\Router;
use Testify\Utils\Router\Response;

session_start();

Router::getInstance(new Request);

include_once 'views/Views.php';
include_once 'controllers/User.php';
include_once 'controllers/Faq.php';
include_once 'controllers/Message.php';
include_once 'controllers/Forum.php';

$router->setNoRouteHandler(function($request) {
    return Response::fromView('/../../views/404.html');
});
