<?php

include_once 'Configuration.php';
include_once 'models/User.php';
include_once 'models/Message.php';
include_once 'models/Role.php';
include_once 'utils/Security.php';
include_once 'utils/API.php';


$router = Router::getInstance();


$router->get(TESTIFY_API_ROOT . 'users/<user_id>/contacts', function($request, $user_id) {
    $errors_arr = array();

    setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = "Vous n'avez pas accès aux informations de cet utilisateur !";

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = "L'utilisateur est introuvable !";
    }

    if(count($errors_arr) === 0) {
        $contacts = Message::getInstance()->getContacts($user);

        return json_encode(array("r" => True, "contacts" => $contacts));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get(TESTIFY_API_ROOT . 'users/<user_id>/<contact_id>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = "Vous n'avez pas accès aux informations de cet utilisateur !";

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = "L'utilisateur est introuvable !";
        else {
            $contact = User::getInstance()->getUser($contact_id);
            if ($contact === null)
                $errors_arr[] = "Le contact est introuvable !";
        }
    }

    if(count($errors_arr) === 0) {
        $messages = Message::getInstance()->getUserContactMessages($user, $contact);

        return json_encode(array("r" => True, "messages" => $messages));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post(TESTIFY_API_ROOT . 'users/<user_id>/<contact_id>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = "Vous n'avez pas accès aux informations de cet utilisateur !";

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = "L'utilisateur est introuvable !";
        else {
            $contact = User::getInstance()->getUser($contact_id);
            if ($contact === null)
                $errors_arr[] = "Le contact est introuvable !";
        }
    }

    if(!isset($request->getBody()['message']) || empty($request->getBody()['message']))
        $errors_arr[] = "Pas de message donné !";

    if(count($errors_arr) === 0) {
        $message = Security::protect($request->getBody()['message']);

        $message = Message::getInstance()->createMessage($user, $contact, $message);
        return json_encode(array("r" => True, "message" => $message));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});
