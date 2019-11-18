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

$router->get('/dashboard/chat/user/<user_id>', function($request, $user_id) {
    $context = array('contact_id' => $user_id);

    return Response::fromView('dashboard/chat_user.html', $context);
});

$router->get('/dashboard/admin', function($request) {
    return Response::fromView('dashboard/admin.html');
});

$router->get('/dashboard/forum', function($request) {
    return Response::fromView('dashboard/forum.html');
});

$router->get('/dashboard/forum/category/<cat_id>', function($request, $cat_id) {
    $context = array('category_id' => $cat_id);

    return Response::fromView('dashboard/forum_category.html', $context);
});

$router->get('/dashboard/forum/post/<post_id>', function($request, $post_id) {
    $context = array('post_id' => $post_id);

    return Response::fromView('dashboard/forum_post.html', $context);
});
