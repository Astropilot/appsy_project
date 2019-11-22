<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\User;
use \Testify\Model\UserInvite;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;

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

    if(!User::getInstance()->userExist($email, $password)) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NO_USER'), 404);
    }

    $user_id = User::getInstance()->getUserID($email);
    $user = User::getInstance()->getUser($user_id);
    $_SESSION['email'] = $user['email'];
    $_SESSION['id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    return new Response(
        json_encode(array("user" => $user))
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

    $invite = UserInvite::getInstance()->getValidInvite($token, $email);
    if(!$invite) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_INVITE_NOT_FOUND'), 404);
    }

    return new Response(
        json_encode(array("invite" => $invite))
    );
});

$router->get('/api/users/<userid:int>', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOACCESS'), 403);

    $user = User::getInstance()->getUser($user_id);
    if(!$user) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);
    }
    return new Response(
        json_encode(array("user" => $user))
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

    $user = User::getInstance()->getUser($user_id, true);
    if(!$user) {
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_NOT_FOUND'), 404);
    }

    $email = isset($data['email']) ? $data['email'] : $user['email'];
    $lastname = isset($data['lastname']) ? $data['lastname'] : $user['lastname'];
    $firstname = isset($data['firstname']) ? $data['firstname'] : $user['firstname'];
    $role = $user['role'];
    $banned = $user['banned'];

    if (isset($data['password']) && !empty($data['password'])) {
        $password = Security::hashPass($data['password'], Config::HASH_SALT);
    } else
        $password = $user['password'];

    $res = User::getInstance()->updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
    if ($res) {
        return new Response(
            json_encode(array("message" => I18n::getInstance()->translate('API_USER_UPDATE_SUCCESS')))
        );
    } else
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_UPDATE_ERROR'), 500);
});

$router->delete('/api/users/<user_id:int>', function($request, $user_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    if (User::getInstance()->deleteUser($user_id)) {
        return new Response(
            '',
            204
        );
    } else {
        return API::makeResponseError("An unexcepted error occured while deleting user!", 500);
    }
});

$router->post('/api/contacts/search', function($request) {
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if(!isset($data['search']))
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_SEARCH_NO_CRITERIA'), 400);

    $search = $data['search'];
    $contacts = User::getInstance()->findContacts($search, !Role::isUser());

    return new Response(
        json_encode(array("contacts" => $contacts))
    );
});

$router->get('/api/users/logoff', function($request) {
    API::setAPIHeaders();

    if(isset($_SESSION['email']) && isset($_SESSION['id'])) {
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        unset($_SESSION['id']);
        session_destroy();
        return new Response('');
    }
    else
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_DECONNECT_ERROR'), 500);
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

    $invite = UserInvite::getInstance()->getValidInvite($token, $email);
    if(!$invite)
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_CREATE_INVITE_EXPIRED'), 404);

    $res = User::getInstance()->createUser(
        $invite['email'],
        $firstname,
        $lastname,
        $invite['role'],
        Security::hashPass($password, Config::HASH_SALT)
    );
    if ($res) {
        UserInvite::getInstance()->unActiveInvite($invite['id']);
        return new Response(
            json_encode(array("message" => I18n::getInstance()->translate('API_USER_CREATE_USER_SUCCESS'))),
            201
        );
    } else
        return API::makeResponseError(I18n::getInstance()->translate('API_USER_CREATE_USER_ERROR'), 500);
});
