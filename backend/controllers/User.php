<?php

include_once 'Configuration.php';
include_once 'models/User.php';
include_once 'utils/Security.php';


$router = Router::getInstance();


$router->post($APPSY_PREFIX . 'users/login', function($request) {
    $errors_arr = array();

    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = "L'identifiant est vide !";
    if(!isset($request->getBody()['password']) || empty($request->getBody()['password']))
        $errors_arr[] = "Le mot de passe est vide !";

    if(count($errors_arr) === 0) {
        $email = Security::protect($request->getBody()['email']);
        $password = Security::hasPass($request->getBody()['password']);

        if(!User::getInstance()->userExist($email, $password)) {
            $errors_arr[] = "Le couple identifiant/mot de passe est incorrect !";
        }

        if(count($errors_arr) === 0) {
            $user_id = User::getInstance()->getUserID($email);
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $user_id;
            $_SESSION['role'] = User::getInstance()->getUserRole($user_id);

            return json_encode(array("r" => True, "user_id" => $user_id));
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post($APPSY_PREFIX . 'users/<userid>', function($request, $user_id) {
    $errors_arr = array();

    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = "Vous n'avez pas accès aux informations de cet utilisateur !";

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null) {
            $errors_arr[] = "L'utilisateur est introuvable !";
        } else
            return json_encode(array("r" => True, "user" => $user));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get($APPSY_PREFIX . 'users/logoff', function($request) {
    if(isset($_SESSION['email']) && isset($_SESSION['id'])) {
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        unset($_SESSION['id']);
        session_destroy();
        return json_encode(array("r" => True));
    }
    else
        return json_encode(array("r" => False));
});
