<?php

use Testify\Router\Router;
use Testify\Model\Forum;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;

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
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_NO_NAME_GIVEN');

    if(count($errors_arr) === 0) {
        $name = Security::protect($request->getBody()['name']);

        $category = Forum::getInstance()->createCategory($name);
        return json_encode(array("r" => True, "category" => $category));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/forum/categories/<category_id>/posts', function($request, $category_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();

    $category = Forum::getInstance()->getCategory($category_id);
    if ($category === null)
        $errors_arr[] = I18n::getInstance()->translate('API_FORUM_CATEGORY_NOT_FOUND');

    if (count($errors_arr) === 0) {
        $posts = Forum::getInstance()->getPosts($category_id);
        return json_encode(array("r" => True, "category" => $category, "posts" => $posts));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});
