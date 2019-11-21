<?php

use Testify\Router\Router;
use Testify\Router\Response;
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
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getInstance()->getUser($user_id);
    if($user === null)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $contacts = $paginator->paginate(Message::getInstance()->getContacts($user));

    return new Response(
        json_encode(array(
            "contacts" => $contacts['data'],
            "paginator" => $contacts['paginator']
        ))
    );
});

$router->get('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    $user = User::getInstance()->getUser($user_id);
    if($user === null)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $contact = User::getInstance()->getUser($contact_id);
    if ($contact === null)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND'), 404);

    $messages = Message::getInstance()->getUserContactMessages($user, $contact);

    return new Response(
        json_encode(array("contact" => $contact, "messages" => $messages))
    );
});

$router->post('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    $user = User::getInstance()->getUser($user_id);
    if($user === null)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $contact = User::getInstance()->getUser($contact_id);
    if ($contact === null)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND'), 404);

    if(!isset($data['message']) || empty($data['message']))
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NO_MESSAGE_GIVEN'), 400);

    $message = $data['message'];

    $message = Message::getInstance()->createMessage($user, $contact, $message);
    if($message !== null) {
        return new Response(
            json_encode(array("message" => $message)),
            201
        );
    } else {
        return API::makeResponseError("An unexcepted error occured while creating message!", 500);
    }
});
