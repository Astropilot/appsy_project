<?php

include_once 'Configuration.php';
include_once 'utils/router/Response.php';

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
