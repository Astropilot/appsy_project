<?php

include_once 'Configuration.php';

use Testify\Utils\Router\Router;
use Testify\Utils\Router\Response;

$router = Router::getInstance();


$router->get(TESTIFY_ROOT, function($request) {
    return Response::fromView('/../../views/home/home.html');
});

$router->get(TESTIFY_ROOT . 'connexion', function($request) {
    return Response::fromView('/../../views/home/connexion.html');
});

$router->get(TESTIFY_ROOT . 'inscription', function($request) {
    return Response::fromView('/../../views/home/inscription.html');
});

$router->get(TESTIFY_ROOT . 'dashboard', function($request) {
    return Response::fromView('/../../views/dashboard/home.html');
});

$router->get(TESTIFY_ROOT . 'dashboard/chat', function($request) {
    return Response::fromView('/../../views/dashboard/chat.html');
});

$router->get(TESTIFY_ROOT . 'dashboard/chat/user', function($request) {
    return Response::fromView('/../../views/dashboard/chat_user.html');
});

$router->get(TESTIFY_ROOT . 'dashboard/forum', function($request) {
    return Response::fromView('/../../views/dashboard/forum.html');
});
