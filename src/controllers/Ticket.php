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
    $data = $request->getData();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKET_NOACCESS'), 403);

    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getUser($user_id);
    if($user === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);
    }

    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $tickets = Ticket::getTicketsFromUser($user);
    if ($tickets === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKET_GET_TICKETS_ERROR'), 500);

    $tickets = $paginator->paginate($tickets);

    return new Response(
        json_encode(array(
            'tickets' => $tickets['data'],
            'paginator' => $tickets['paginator']
        ))
    );
});

$router->post('/api/users/<user_id:int>/tickets', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('title'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKETS_NO_NAME_GIVEN');
    if(!$data->existAndNotEmpty('content'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKETS_NO_DESCRIPTION_GIVEN');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $title = $data->get('title');
    $content = $data->get('content');

    $ticket = Ticket::createTicket($user_id, $title, $content, 0);
    if($ticket === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKETS_CREATE_ERROR'), 500);

    return new Response(
        json_encode(array('ticket' => $ticket)),
        201
    );
});

$router->get('/api/tickets/<ticket_id:int>/comments', function($request, $ticket_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_TICKET_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $ticket = Ticket::getTicket($ticket_id);
    if($ticket === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKET_NOT_FOUND'), 404);
    }

    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $comments = Ticket::getTicketComments($ticket);
    if ($comments === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_TICKET_GET_COMMENTS_ERROR'), 500);

    $comments = $paginator->paginate($comments);

    return new Response(
        json_encode(array(
            'ticket' => $ticket,
            'comments' => $comments['data'],
            'paginator' => $comments['paginator']
        ))
    );
});
