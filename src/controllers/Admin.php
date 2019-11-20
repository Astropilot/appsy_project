<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\User;
use Testify\Model\UserInvite;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;
use Testify\Component\Mail;

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

    if(count($errors_arr) === 0) {
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

        if (!$res)
            $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_CREATE_ERROR');
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
                Response::fromView('mails/invite.html', $context, $lang)
            );

            if ($mail->sendMail())
                return json_encode(array("r" => True, "message" => "$firstname à bien été invité !"));
            else
                $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_SEND_ERROR');
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->put('/admin/api/users/<userid:int>', function($request, $user_id) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $user = User::getInstance()->getUser($user_id, true);

    if($user === null) {
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_USER_NOT_FOUND');
    } else {
        $email = isset($data['email']) ? $data['email'] : $user['email'];
        $password = isset($data['password']) ? Security::hashPass($data['password'], Config::HASH_SALT) : $user['password'];
        $lastname = isset($data['lastname']) ? $data['lastname'] : $user['lastname'];
        $firstname = isset($data['firstname']) ? $data['firstname'] : $user['firstname'];
        $role = isset($data['role']) ? $data['role'] : $user['role'];
        $banned = isset($data['banned']) ? $data['banned'] : $user['banned'];

        $res = User::getInstance()->updateUser($user['id'], $email, $password, $lastname, $firstname, $role, $banned);
        if ($res)
            return json_encode(array("r" => True, "message" => I18n::getInstance()->translate('API_ADMIN_USER_UPDATE_SUCCESS')));
        else
            $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_USER_UPDATE_ERROR');
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});
