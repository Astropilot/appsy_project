<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\User;
use \Testify\Model\UserInvite;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;
use Testify\Component\Paginator;

use Testify\Config;

$router = Router::getInstance();


$router->post('/api/users/login', function($request) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();

    if(!isset($data['email']) || empty($data['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_USERNAME_PROVIDED');
    if(!isset($data['password']) || empty($data['password']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_PASSWORD_PROVIDED');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $email = $data['email'];
    $password = Security::hashPass($data['password'], Config::HASH_SALT);

    if(User::userExist($email, $password) === FALSE) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NO_USER'), 404);
    }

    $user_id = User::getUserID($email);
    if ($user_id === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_GET_USER_ERROR'), 500);

    $user = User::getUser($user_id);
    if ($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_GET_USER_ERROR'), 500);

    $_SESSION['email'] = $user['email'];
    $_SESSION['id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    return new Response(
        json_encode(array('user' => $user))
    );
});

$router->get('/api/users/invite', function($request) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();

    if(!isset($data['token']) || empty($data['token']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_TOKEN');
    if(!isset($data['email']) || empty($data['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_EMAIL');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $token = $data['token'];
    $email = $data['email'];

    $invite = UserInvite::getValidInvite($token, $email);
    if($invite === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_INVITE_NOT_FOUND'), 404);

    return new Response(
        json_encode(array('invite' => $invite))
    );
});

$router->get('/api/users/<userid:int>', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOACCESS'), 403);

    $user = User::getUser($user_id);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);

    return new Response(
        json_encode(array('user' => $user))
    );
});

$router->put('/api/users/<userid:int>', function($request, $user_id) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NOACCESS');

    if (isset($data['email']) && empty($data['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_EMAIL');

    if (isset($data['lastname']) && empty($data['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_LASTNAME');

    if (isset($data['firstname']) && empty($data['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_FIRSTNAME');

    if (isset($data['password']) && isset($data['passwordcheck'])) {
        if (!empty($data['password']) && $data['password'] !== $data['passwordcheck'])
            $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_PASSWORD_CHECK_ERROR');
    }

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getUser($user_id, true);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);

    $email = isset($data['email']) ? $data['email'] : $user['email'];
    $lastname = isset($data['lastname']) ? $data['lastname'] : $user['lastname'];
    $firstname = isset($data['firstname']) ? $data['firstname'] : $user['firstname'];
    $role = $user['role'];
    $banned = $user['banned'];

    if (isset($data['password']) && !empty($data['password'])) {
        $password = Security::hashPass($data['password'], Config::HASH_SALT);
    } else
        $password = $user['password'];

    $res = User::updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
    if ($res === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_UPDATE_ERROR'), 500);

    return new Response(
        json_encode(array("message" => I18n::getInstance()->translate('API_USER_UPDATE_SUCCESS')))
    );
});

$router->delete('/api/users/<user_id:int>', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    if (User::deleteUser($user_id) === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_DELETE_ERROR'), 500);

    return new Response(
        '',
        204
    );
});

$router->post('/api/contacts/search', function($request) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if(!isset($data['search']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_SEARCH_NO_CRITERIA');
    if(!isset($data['page']) || empty($data['page']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOPAGE');
    if(!isset($data['pageSize']) || empty($data['pageSize']))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $search = $data['search'];
    $page = $data['page'];
    $pageSize = $data['pageSize'];

    $paginator = new Paginator($page, $pageSize);
    $contacts = User::findContacts($search, !Role::isUser());
    if ($contacts === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_GET_CONTACTS_ERROR'), 500);

    $contacts = $paginator->paginate($contacts);

    return new Response(
        json_encode(array(
            'contacts' => $contacts['data'],
            'paginator' => $contacts['paginator']
        ))
    );
});

$router->get('/api/users/logoff', function($request) {
    API::setAPIHeaders();

    if(!isset($_SESSION['email']) || !isset($_SESSION['id']))
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_DECONNECT_ERROR'), 500);

    unset($_SESSION['email']);
    unset($_SESSION['role']);
    unset($_SESSION['id']);
    session_destroy();
    return new Response('', 204);
});

$router->post('/api/users', function($request) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();

    if(!isset($data['token']) || empty($data['token']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_TOKEN');
    if(!isset($data['email']) || empty($data['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_EMAIL');
    if(!isset($data['firstname']) || empty($data['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_FIRSTNAME');
    if(!isset($data['lastname']) || empty($data['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_LASTNAME');
    if(!isset($data['password']) || empty($data['password']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORD');
    if(!isset($data['password_check']) || empty($data['password_check']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORDCHECK');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $token = $data['token'];
    $email = $data['email'];
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $password = $data['password'];
    $password_check = $data['password_check'];

    if ($password !== $password_check)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_CREATE_PASSWORD_NOT_MATCH'), 400);

    $invite = UserInvite::getValidInvite($token, $email);
    if($invite === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_CREATE_INVITE_EXPIRED'), 404);

    $res = User::createUser(
        $invite['email'],
        $firstname,
        $lastname,
        $invite['role'],
        Security::hashPass($password, Config::HASH_SALT)
    );
    if ($res === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_CREATE_USER_ERROR'), 500);

    UserInvite::unActiveInvite($invite['id']);

    return new Response(
        json_encode(array('message' => I18n::getInstance()->translate('API_USER_CREATE_USER_SUCCESS'))),
        201
    );
});
