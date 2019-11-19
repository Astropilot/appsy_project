<?php

use Testify\Router\Router;
use Testify\Model\User;
use Testify\Model\Message;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\Paginator;
use Testify\Component\I18n;

$router = Router::getInstance();


$router->get('/api/users/<user_id:int>/contacts', function($request, $user_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOACCESS');

    if(!isset($request->getBody()['page']) || empty($request->getBody()['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOPAGE');
    if(!isset($request->getBody()['pageSize']) || empty($request->getBody()['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOSIZEPAGE');

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND');
    }

    if(count($errors_arr) === 0) {
        $page = Security::protect($request->getBody()['page']);
        $pageSize = Security::protect($request->getBody()['pageSize']);

        $paginator = new Paginator($page, $pageSize);
        $contacts = $paginator->paginate(Message::getInstance()->getContacts($user));

        return json_encode(array(
            "r" => True,
            "contacts" => $contacts['data'],
            "paginator" => $contacts['paginator']
        ));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOACCESS');

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND');
        else {
            $contact = User::getInstance()->getUser($contact_id);
            if ($contact === null)
                $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND');
        }
    }

    if(count($errors_arr) === 0) {
        $messages = Message::getInstance()->getUserContactMessages($user, $contact);

        return json_encode(array("r" => True, "contact" => $contact, "messages" => $messages));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOACCESS');

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null)
            $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND');
        else {
            $contact = User::getInstance()->getUser($contact_id);
            if ($contact === null)
                $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND');
        }
    }

    if(!isset($request->getBody()['message']) || empty($request->getBody()['message']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NO_MESSAGE_GIVEN');

    if(count($errors_arr) === 0) {
        $message = Security::protect($request->getBody()['message']);

        $message = Message::getInstance()->createMessage($user, $contact, $message);
        return json_encode(array("r" => True, "message" => $message));
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});
