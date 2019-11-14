<?php

use Testify\Router\Router;
use Testify\Model\User;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;

$router = Router::getInstance();


$router->post('/api/users/login', function($request) {
    $errors_arr = array();

    API::setAPIHeaders();

    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = "L'identifiant est vide !";
    if(!isset($request->getBody()['password']) || empty($request->getBody()['password']))
        $errors_arr[] = "Le mot de passe est vide !";

    if(count($errors_arr) === 0) {
        $email = Security::protect($request->getBody()['email']);
        $password = Security::hashPass($request->getBody()['password']);

        if(!User::getInstance()->userExist($email, $password)) {
            $errors_arr[] = "Le couple identifiant/mot de passe est incorrect !";
        }

        if(count($errors_arr) === 0) {
            $user_id = User::getInstance()->getUserID($email);
            $user = User::getInstance()->getUser($user_id);
            $_SESSION['email'] = $user['email'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            return json_encode(array("r" => True, "user" => $user));
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/<userid>', function($request, $user_id) {
    $errors_arr = array();

    API::setAPIHeaders();
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

$router->post('/api/contacts/search', function($request) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if(!isset($request->getBody()['search']) || empty($request->getBody()['search']))
        $errors_arr[] = "Pas de critère donné !";

    if(count($errors_arr) === 0) {
        $search = Security::protect($request->getBody()['search']);
        $contacts = User::getInstance()->findContacts($search, !Role::isUser());

        return json_encode(array("r" => True, "contacts" => $contacts));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/logoff', function($request) {
    API::setAPIHeaders();

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
