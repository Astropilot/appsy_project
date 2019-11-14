<?php

use Testify\Router\Router;
use Testify\Router\Response;

$router = Router::getInstance();


$router->get('/', function($request) {
    return Response::fromView('home/home.html');
});

$router->get('/connexion', function($request) {
    return Response::fromView('home/connexion.html');
});

$router->get('/inscription', function($request) {
    return Response::fromView('home/inscription.html');
});

$router->get('/dashboard', function($request) {
    return Response::fromView('dashboard/home.html');
});

$router->get('/dashboard/chat', function($request) {
    return Response::fromView('dashboard/chat.html');
});

$router->get('/dashboard/chat/user', function($request) {
    return Response::fromView('dashboard/chat_user.html');
});

$router->get('/dashboard/forum', function($request) {
    return Response::fromView('dashboard/forum.html');
});
