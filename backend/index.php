<?php

include_once 'router/Request.php';
include_once 'router/Router.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

Router::getInstance(new Request);

include_once 'controllers/User.php';
include_once 'controllers/Faq.php';
include_once 'controllers/Message.php';
