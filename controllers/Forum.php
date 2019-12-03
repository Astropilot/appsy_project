<?php

include_once 'Configuration.php';
include_once 'models/Forum.php';
include_once 'models/Role.php';
include_once 'utils/Security.php';
include_once 'utils/API.php';


$router = Router::getInstance();


$router->get(TESTIFY_API_ROOT . 'forums/categories', function($request) {
    setAPIHeaders();
    Security::checkAPIConnected();

    $categories = Forum::getInstance()->getCategories();
    return json_encode(array("r" => True, "categories" => $categories));
});
