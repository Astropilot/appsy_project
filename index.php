<?php

include_once 'utils/router/Request.php';
include_once 'utils/router/Router.php';

session_start();

Router::getInstance(new Request);

include_once 'views/Views.php';
include_once 'controllers/User.php';
include_once 'controllers/Faq.php';
include_once 'controllers/Message.php';

$router->setNoRouteHandler(function($request) {
    return Response::fromView('/../../views/404.html');
});
