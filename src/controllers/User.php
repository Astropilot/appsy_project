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
    $data = $request->getData();

    API::setAPIHeaders();

    if(!$data->existAndNotEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_USERNAME_PROVIDED');
    if(!$data->existAndNotEmpty('password'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_PASSWORD_PROVIDED');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $email = $data->get('email');
    $password = Security::hashPass($data->get('password'), Config::HASH_SALT);

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
    $data = $request->getData();

    API::setAPIHeaders();

    if(!$data->existAndNotEmpty('token'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_TOKEN');
    if(!$data->existAndNotEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_EMAIL');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $token = $data->get('token');
    $email = $data->get('email');

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
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NOACCESS');

    if ($data->existAndEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_EMAIL');

    if ($data->existAndEmpty('lastname'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_LASTNAME');

    if ($data->existAndEmpty('firstname'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_FIRSTNAME');

    if ($data->isExist('password') && $data->isExist('passwordcheck')) {
        $pass = $data->get('password');
        $passcheck = $data->get('passwordcheck');
        if (!$data->isEmpty('password') && $pass !== $passcheck)
            $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_PASSWORD_CHECK_ERROR');
    }

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $user = User::getUser($user_id, true);
    if($user === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);

    $email = $data->getWithDefault('email', $user['email']);
    $lastname = $data->getWithDefault('lastname', $user['lastname']);
    $firstname = $data->getWithDefault('firstname', $user['firstname']);
    $role = $user['role'];
    $banned = $user['banned'];

    if ($data->existAndNotEmpty('password')) {
        $password = Security::hashPass($data->get('password'), Config::HASH_SALT);
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
    $data = $request->getData();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if(!$data->isExist('search'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_SEARCH_NO_CRITERIA');
    if(!$data->existAndNotEmpty('page'))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOPAGE');
    if(!$data->existAndNotEmpty('pageSize'))
        $errors_arr[] = I18n::getInstance()->translate('API_MESSAGE_NOSIZEPAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $search = $data->get('search');
    $page = $data->get('page');
    $pageSize = $data->get('pageSize');

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
    $data = $request->getData();

    API::setAPIHeaders();

    if(!$data->existAndNotEmpty('token'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_TOKEN');
    if(!$data->existAndNotEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_EMAIL');
    if (!filter_var($data->getWithDefault('email', ''), FILTER_VALIDATE_EMAIL))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_EMAIL_NOTVALIDE');
    if(!$data->existAndNotEmpty('firstname'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_FIRSTNAME');
    if(!$data->existAndNotEmpty('lastname'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_LASTNAME');
    if(!$data->existAndNotEmpty('password'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORD');
    if (strlen($data->get('password')) < 6)
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_PASSWORD_SHORT');
    if(!$data->existAndNotEmpty('password_check'))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORDCHECK');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $token = $data->get('token');
    $email = $data->get('email');
    $firstname = $data->get('firstname');
    $lastname = $data->get('lastname');
    $password = $data->get('password');
    $password_check = $data->get('password_check');

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
