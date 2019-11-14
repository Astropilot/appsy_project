<?php

use Testify\Router\Router;
use Testify\Model\User;
use Testify\Model\Message;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\Paginator;

$router = Router::getInstance();


$router->get('/api/users/<user_id>/contacts', function($request, $user_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = "Vous n'avez pas accès aux informations de cet utilisateur !";

    if(!isset($request->getBody()['page']) || empty($request->getBody()['page']))
        $errors_arr[] = "Pas de page recu !";
    if(!isset($request->getBody()['pageSize']) || empty($request->getBody()['pageSize']))
        $errors_arr[] = "Pas de taille de page recu !";

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = "L'utilisateur est introuvable !";
    }

    if(count($errors_arr) === 0) {
        $page = Security::protect($request->getBody()['page']);
        $pageSize = Security::protect($request->getBody()['pageSize']);

        $paginator = new Paginator($page, $pageSize);
        $contacts = $paginator->paginate(Message::getInstance()->getContacts($user));

        return json_encode(array("r" => True, "contacts" => $contacts['data'], "paginator" => $contacts['paginator']));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/<user_id>/<contact_id>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
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

        return json_encode(array("r" => True, "contact" => $contact, "messages" => $messages));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post('/api/users/<user_id>/<contact_id>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
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
