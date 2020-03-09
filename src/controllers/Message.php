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
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getUser($user_id);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $contacts = Message::getContacts($user);

    if ($contacts === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_GET_CONTACTS_ERROR'), 500);

    $contacts = $paginator->paginate($contacts);

    return new Response(
        json_encode(array(
            'contacts' => $contacts['data'],
            'paginator' => $contacts['paginator']
        ))
    );
});

$router->get('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    $user = User::getUser($user_id);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $contact = User::getUser($contact_id);
    if ($contact === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND'), 404);

    $messages = Message::getUserContactMessages($user, $contact);
    if ($messages === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_GET_MESSAGES_ERROR'), 500);

    return new Response(
        json_encode(array('contact' => $contact, 'messages' => $messages))
    );
});

$router->post('/api/users/<user_id:int>/<contact_id:int>/messages', function($request, $user_id, $contact_id) {
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NOACCESS'), 403);

    $user = User::getUser($user_id);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_USER_NOT_FOUND'), 404);

    $contact = User::getUser($contact_id);
    if ($contact === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_CONTACT_NOT_FOUND'), 404);

    if(!$data->existAndNotEmpty('message'))
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_NO_MESSAGE_GIVEN'), 400);

    $message = $data->get('message');

    $message = Message::createMessage($user, $contact, $message);
    if($message === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_MESSAGE_CREATE_ERROR'), 500);

    return new Response(
        json_encode(array('message' => $message)),
        201
    );
});
