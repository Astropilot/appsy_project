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
    if ($_SESSION['role']===0){
        return Response::fromView('dashboard/home_user.html');
    }
    if ($_SESSION['role']===1){
        return Response::fromView('dashboard/home_examiner.html');
    }
    else{
    return Response::fromView('dashboard/home_admin.html');
    }
});

$router->get('/dashboard/profile', function($request) {
    return Response::fromView('dashboard/profile.html');
});

$router->get('/dashboard/tickets', function($request) {
    return Response::fromView('dashboard/tickets.html');
});

$router->get('/dashboard/ticket/<ticket_id:int>', function($request, $ticket_id) {
    $context = array('ticket_id' => $ticket_id);

    return Response::fromView('dashboard/ticket.html', $context);
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
