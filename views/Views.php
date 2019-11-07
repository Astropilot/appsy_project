<?php

include_once 'Configuration.php';
include_once 'utils/router/Response.php';

$router = Router::getInstance();


$router->get(TESTIFY_ROOT, function($request) {
    return Response::fromView('/../../views/landing.html');
});

$router->get(TESTIFY_ROOT . 'connexion', function($request) {
    return Response::fromView('/../../views/connexion.html');
});
