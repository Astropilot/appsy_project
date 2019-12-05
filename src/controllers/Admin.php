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
    $data = $request->getBody();

    if(!isset($data['email']) || empty($data['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_EMAIL');
    if(!isset($data['firstname']) || empty($data['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_FIRSTNAME');
    if(!isset($data['lastname']) || empty($data['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LASTNAME');
    if(!isset($data['role']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_ROLE');
    if(!isset($data['lang']) || empty($data['lang']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LANG');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $email = $data['email'];
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $role = $data['role'];
    $lang = $data['lang'];

    date_default_timezone_set('UTC');

    $datetime = new \DateTime();
    $now = $datetime->format('Y-m-d H:i:s');
    $token = Security::hashPass($email, $now);
    $datetime->add(new \DateInterval('P3D'));
    $expire_date = $datetime->format('Y-m-d');

    $res = UserInvite::getInstance()->createInvite($email, $firstname, $lastname, $role, $token, $expire_date);

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
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $user = User::getInstance()->getUser($user_id, true);

    if($user === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_USER_NOT_FOUND'), 404);
    }

    $email = isset($data['email']) ? $data['email'] : $user['email'];
    $password = isset($data['password']) ? Security::hashPass($data['password'], Config::HASH_SALT) : $user['password'];
    $lastname = isset($data['lastname']) ? $data['lastname'] : $user['lastname'];
    $firstname = isset($data['firstname']) ? $data['firstname'] : $user['firstname'];
    $role = isset($data['role']) ? $data['role'] : $user['role'];
    $banned = isset($data['banned']) ? $data['banned'] : $user['banned'];

    $res = User::getInstance()->updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
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
    $data = $request->getBody();

    if(!isset($data['search']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_SEARCH_NO_CRITERIA');
    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $search = $data['search'];
    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $tickets = Ticket::getInstance()->findTickets($search);
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


$router->post('/admin/api/tickets/<ticket_id:int>/comments', function($request, $ticket_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['author']) || empty($data['author']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_COMMENT_NOAUTHOR');
    if(!isset($data['content']) || empty($data['content']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_TICKET_COMMENT_NOCONTENT');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $author = $data['author'];
    $content = $data['content'];

    $comment = Ticket::getInstance()->createTicketComment($ticket_id, $author, $content);
    if ($comment === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_ADMIN_TICKET_CREATE_COMMENT_ERROR'), 500);

    return new Response(
        json_encode(array(
            'comment' => $comment,
        ))
    );
});
