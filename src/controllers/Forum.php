<?php

use Testify\Router\Router;
use Testify\Model\Forum;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;

$router = Router::getInstance();


$router->get('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $categories = Forum::getInstance()->getCategories();
    return json_encode(array("r" => True, "categories" => $categories));
});

$router->post('/api/forum/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if(!isset($request->getBody()['name']) || empty($request->getBody()['name']))
        $errors_arr[] = "Pas de nom de catégorie donné !";

    if(count($errors_arr) === 0) {
        $name = Security::protect($request->getBody()['name']);

        $category = Forum::getInstance()->createCategory($name);
        return json_encode(array("r" => True, "category" => $category));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});
