<?php

use Testify\Router\Router;
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

    API::setAPIHeaders();

    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_USERNAME_PROVIDED');
    if(!isset($request->getBody()['password']) || empty($request->getBody()['password']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_PASSWORD_PROVIDED');

    if(count($errors_arr) === 0) {
        $email = Security::protect($request->getBody()['email']);
        $password = Security::hashPass($request->getBody()['password'], Config::HASH_SALT);

        if(!User::getInstance()->userExist($email, $password)) {
            $errors_arr[] = I18n::getInstance()->translate('API_USER_NO_USER');
        }

        if(count($errors_arr) === 0) {
            $user_id = User::getInstance()->getUserID($email);
            $user = User::getInstance()->getUser($user_id);
            $_SESSION['email'] = $user['email'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            return json_encode(array("r" => True, "user" => $user));
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/invite', function($request) {
    $errors_arr = array();

    API::setAPIHeaders();

    if(!isset($request->getBody()['token']) || empty($request->getBody()['token']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_TOKEN');
    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NO_EMAIL');

    if(count($errors_arr) === 0) {
        $token = Security::protect($request->getBody()['token']);
        $email = Security::protect($request->getBody()['email']);

        $invite = UserInvite::getInstance()->getValidInvite($token, $email);
        if($invite === null) {
            $errors_arr[] = I18n::getInstance()->translate('API_USER_INVITE_NOT_FOUND');
        } else
            return json_encode(array("r" => True, "invite" => $invite));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/<userid:int>', function($request, $user_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NOACCESS');

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id);
        if($user === null) {
            $errors_arr[] = I18n::getInstance()->translate('API_USER_NOT_FOUND');
        } else
            return json_encode(array("r" => True, "user" => $user));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->put('/api/users/<userid:int>', function($request, $user_id) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if (intval($user_id) !== $_SESSION['id'])
        $errors_arr[] = I18n::getInstance()->translate('API_USER_NOACCESS');

    if (isset($request->getBody()['email']) && empty($request->getBody()['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_EMAIL');

    if (isset($request->getBody()['lastname']) && empty($request->getBody()['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_LASTNAME');

    if (isset($request->getBody()['firstname']) && empty($request->getBody()['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_NO_FIRSTNAME');

    if (isset($request->getBody()['password']) && isset($request->getBody()['passwordcheck'])) {
        if (!empty($request->getBody()['password']) && $request->getBody()['password'] !== $request->getBody()['passwordcheck'])
            $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_PASSWORD_CHECK_ERROR');
    }

    if(count($errors_arr) === 0) {
        $user = User::getInstance()->getUser($user_id, true);

        if($user === null) {
            $errors_arr[] = I18n::getInstance->translate('API_USER_NOT_FOUND');
        } else {
            $email = isset($request->getBody()['email']) ? $request->getBody()['email'] : $user['email'];
            $lastname = isset($request->getBody()['lastname']) ? $request->getBody()['lastname'] : $user['lastname'];
            $firstname = isset($request->getBody()['firstname']) ? $request->getBody()['firstname'] : $user['firstname'];
            $role = $user['role'];
            $banned = $user['banned'];

            if (isset($request->getBody()['password']) && !empty($request->getBody()['password'])) {
                $password = Security::hashPass($request->getBody()['password'], Config::HASH_SALT);
            } else
                $password = $user['password'];

            $res = User::getInstance()->updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
            if ($res)
                return json_encode(array("r" => True, "message" => I18n::getInstance()->translate('API_USER_UPDATE_SUCCESS')));
            else
                $errors_arr[] = I18n::getInstance()->translate('API_USER_UPDATE_ERROR');
        }
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->post('/api/contacts/search', function($request) {
    $errors_arr = array();

    API::setAPIHeaders();
    Security::checkAPIConnected();

    if(!isset($request->getBody()['search']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_SEARCH_NO_CRITERIA');

    if(count($errors_arr) === 0) {
        $search = Security::protect($request->getBody()['search']);
        $contacts = User::getInstance()->findContacts($search, !Role::isUser());

        return json_encode(array("r" => True, "contacts" => $contacts));
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->get('/api/users/logoff', function($request) {
    API::setAPIHeaders();

    if(isset($_SESSION['email']) && isset($_SESSION['id'])) {
        unset($_SESSION['email']);
        unset($_SESSION['role']);
        unset($_SESSION['id']);
        session_destroy();
        return json_encode(array("r" => True));
    }
    else
        return json_encode(array("r" => False, "errors" => array(I18n::getInstance()->translate('API_USER_DECONNECT_ERROR'))));
});

$router->post('/api/users', function($request) {
    $errors_arr = array();

    API::setAPIHeaders();

    if(!isset($request->getBody()['token']) || empty($request->getBody()['token']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_TOKEN');
    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_EMAIL');
    if(!isset($request->getBody()['firstname']) || empty($request->getBody()['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_FIRSTNAME');
    if(!isset($request->getBody()['lastname']) || empty($request->getBody()['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_LASTNAME');
    if(!isset($request->getBody()['password']) || empty($request->getBody()['password']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORD');
    if(!isset($request->getBody()['password_check']) || empty($request->getBody()['password_check']))
        $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_NO_PASSWORDCHECK');

    if(count($errors_arr) === 0) {
        $token = Security::protect($request->getBody()['token']);
        $email = Security::protect($request->getBody()['email']);
        $firstname = Security::protect($request->getBody()['firstname']);
        $lastname = Security::protect($request->getBody()['lastname']);
        $password = Security::protect($request->getBody()['password']);
        $password_check = Security::protect($request->getBody()['password_check']);

        if ($password !== $password_check)
            $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_PASSWORD_NOT_MATCH');
    }

    if (count($errors_arr) === 0) {
        $invite = UserInvite::getInstance()->getValidInvite($token, $email);
        if($invite === null)
            $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_INVITE_EXPIRED');
        else {
            $res = User::getInstance()->createUser(
                $invite['email'],
                $firstname,
                $lastname,
                $invite['role'],
                Security::hashPass($password, Config::HASH_SALT)
            );
            if ($res) {
                UserInvite::getInstance()->unActiveInvite($invite['id']);
                return json_encode(array("r" => True, "message" => I18n::getInstance()->translate('API_USER_CREATE_USER_SUCCESS')));
            } else
                $errors_arr[] = I18n::getInstance()->translate('API_USER_CREATE_USER_ERROR');
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});
