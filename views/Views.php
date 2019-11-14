<?php

use Testify\Utils\Router\Router;
use Testify\Utils\Router\Response;

$router = Router::getInstance();


$router->get('/', function($request) {
    return Response::fromView('/../../views/home/home.html');
});

$router->get('/connexion', function($request) {
    return Response::fromView('/../../views/home/connexion.html');
});

$router->get('/inscription', function($request) {
    return Response::fromView('/../../views/home/inscription.html');
});

$router->get('/dashboard', function($request) {
    return Response::fromView('/../../views/dashboard/home.html');
});

$router->get('/dashboard/chat', function($request) {
    return Response::fromView('/../../views/dashboard/chat.html');
});

$router->get('/dashboard/chat/user', function($request) {
    return Response::fromView('/../../views/dashboard/chat_user.html');
});

$router->get('/dashboard/forum', function($request) {
    return Response::fromView('/../../views/dashboard/forum.html');
});
