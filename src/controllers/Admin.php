<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\User;
use Testify\Model\UserInvite;
use Testify\Model\Ticket;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;
use Testify\Component\Mail;
use Testify\Component\Paginator;

use \Testify\Config;

$router = Router::getInstance();


$router->post('/admin/api/users', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_EMAIL');
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_EMAIL_NOTVALIDE');
    if(!$data->existAndNotEmpty('firstname'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_FIRSTNAME');
    if(!$data->existAndNotEmpty('lastname'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LASTNAME');
    if(!$data->existAndNotEmpty('role'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_ROLE');
    if(!$data->existAndNotEmpty('lang'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LANG');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $email = $data->get('email');
    $firstname = $data->get('firstname');
    $lastname = $data->get('lastname');
    $role = $data->get('role');
    $lang = $data->get('lang');

    date_default_timezone_set('UTC');

    $datetime = new \DateTime();
    $now = $datetime->format('Y-m-d H:i:s');
    $token = Security::hashPass($email, $now);
    $datetime->add(new \DateInterval('P3D'));
    $expire_date = $datetime->format('Y-m-d');

    $res = UserInvite::createInvite($email, $firstname, $lastname, $role, $token, $expire_date);

    if ($res === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_INVITE_CREATE_ERROR'), 500);
    else {
        $context = array(
            'user' => "$lastname $firstname",
            'link' => "http://localhost/inscription/$token/$email"
        );

        $mail = new Mail(
            array(
                'smtp_host' => getenv('SMTP_HOST'),
                'username' => getenv('MAIL_USERNAME'),
                'password' => getenv('MAIL_PASSWORD'),
                'name' => 'Testify'
            ),
            $email,
            I18n::getInstance()->translate('API_ADMIN_INVITE_MAIL_TITLE', $lang),
            Response::fromView('mails/invite.html', $context, $lang)->getContent()
        );

        if ($mail->sendMail() === FALSE)
            return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_INVITE_SEND_ERROR'), 500);

        return new Response(
            json_encode(array('message' => "$firstname " . I18n::getInstance()->translate('API_ADMIN_INVITE_SEND_SUCCESS'))),
            201
        );
    }
});

$router->put('/admin/api/users/<userid:int>', function($request, $user_id) {
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $user = User::getUser($user_id, true);

    if($user === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_USER_NOT_FOUND'), 404);
    }

    $email = $data->getWithDefault('email', $user['email']);
    if ($data->existAndNotEmpty('password'))
        $password = Security::hashPass($data->get('password'), Config::HASH_SALT);
    else
        $password = $user['password'];
    $lastname = $data->getWithDefault('lastname', $user['lastname']);
    $firstname = $data->getWithDefault('firstname', $user['firstname']);
    $role = $data->getWithDefault('role', $user['role']);
    $banned = $data->getWithDefault('banned', $user['banned']);

    $res = User::updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
    if ($res === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_USER_UPDATE_ERROR'), 500);

    return new Response(
        json_encode(array('message' => I18n::getInstance()->translate('API_ADMIN_USER_UPDATE_SUCCESS')))
    );
});

$router->post('/admin/api/tickets', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->isExist('search'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_SEARCH_NO_CRITERIA');
    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_SEARCH_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_SEARCH_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $search = $data->get('search');
    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

    $paginator = new Paginator($page, $pageSize);
    $tickets = Ticket::findTickets($search);
    if ($tickets === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_TICKET_GET_TICKETS_ERROR'), 500);

    $tickets = $paginator->paginate($tickets);

    return new Response(
        json_encode(array(
            'tickets' => $tickets['data'],
            'paginator' => $tickets['paginator']
        ))
    );
});

$router->put('/admin/api/tickets/<ticket_id:int>', function($request, $ticket_id) {
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $ticket = Ticket::getTicket($ticket_id);

    if($ticket === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_TICKET_NOT_FOUND'), 404);
    }

    $status = $data->getWithDefault('status', $ticket['status']);

    $res = Ticket::updateTicketStatus($ticket['id'], $status);
    if ($res === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_TICKET_UPDATE_ERROR'), 500);

    return new Response(
        json_encode(array('message' => I18n::getInstance()->translate('API_ADMIN_TICKET_UPDATE_SUCCESS')))
    );
});


$router->post('/admin/api/tickets/<ticket_id:int>/comments', function($request, $ticket_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('author'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_COMMENT_NOAUTHOR');
    if(!$data->existAndNotEmpty('content'))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_COMMENT_NOCONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $author = $data->get('author');
    $content = $data->get('content');

    $comment = Ticket::createTicketComment($ticket_id, $author, $content);
    if ($comment === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_TICKET_CREATE_COMMENT_ERROR'), 500);

    return new Response(
        json_encode(array(
            'comment' => $comment,
        ))
    );
});
