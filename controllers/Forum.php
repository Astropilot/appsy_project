<?php

use Testify\Utils\Router\Router;
use Testify\Model\Forum;
use Testify\Model\Role;
use Testify\Utils\Security;
use Testify\Utils\API;

$router = Router::getInstance();


$router->get('/api/forums/categories', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $categories = Forum::getInstance()->getCategories();
    return json_encode(array("r" => True, "categories" => $categories));
});
