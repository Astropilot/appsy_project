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

$router->get('/faq', function($request) {
    return Response::fromView('home/faq.html');
});

$router->get('/contact', function($request) {
    return Response::fromView('home/contact.html');
});

$router->get('/cgu', function($request) {
    return Response::fromView('home/cgu.html');
});

$router->get('/inscription/<token:str>/<email:str>', function($request, $token, $email) {
    $context = array('token' => $token, 'email' => $email);

    return Response::fromView('home/inscription.html', $context);
});

$router->get('/dashboard', function($request) {
    return Response::fromView('dashboard/home.html');
});

$router->get('/dashboard/profile', function($request) {
    return Response::fromView('dashboard/profile.html');
});

$router->get('/dashboard/chat', function($request) {
    return Response::fromView('dashboard/chat.html');
});

$router->get('/dashboard/chat/user/<user_id:int>', function($request, $user_id) {
    $context = array('contact_id' => $user_id);

    return Response::fromView('dashboard/chat_user.html', $context);
});

$router->get('/dashboard/admin', function($request) {
    return Response::fromView('dashboard/admin.html');
});

$router->get('/dashboard/forum', function($request) {
    return Response::fromView('dashboard/forum.html');
});

$router->get('/dashboard/forum/category/<cat_id:int>', function($request, $cat_id) {
    $context = array('category_id' => $cat_id);

    return Response::fromView('dashboard/forum_category.html', $context);
});

$router->get('/dashboard/forum/post/<post_id:int>', function($request, $post_id) {
    $context = array('post_id' => $post_id);

    return Response::fromView('dashboard/forum_post.html', $context);
});

$router->get('/404', function($request) {
    return Response::fromView('404.html', null, null, 404);
});
