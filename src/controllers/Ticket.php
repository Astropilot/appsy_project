<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\User;
use Testify\Model\Ticket;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\Paginator;
use Testify\Component\I18n;

$router = Router::getInstance();

$router->get('/api/users/<user_id:int>/tickets', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getBody();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKET_NOACCESS'), 403);

    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getInstance()->getUser($user_id);
    if(!$user) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);
    }

    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $tickets = $paginator->paginate(Ticket::getInstance()->getTicketsFromUser($user));
    return new Response(
        json_encode(array(
            'tickets' => $tickets['data'],
            'paginator' => $tickets['paginator']
        ))
    );
});
