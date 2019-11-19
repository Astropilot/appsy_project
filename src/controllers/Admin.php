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

    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_EMAIL');
    if(!isset($request->getBody()['firstname']) || empty($request->getBody()['firstname']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_FIRSTNAME');
    if(!isset($request->getBody()['lastname']) || empty($request->getBody()['lastname']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LASTNAME');
    if(!isset($request->getBody()['role']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_ROLE');
    if(!isset($request->getBody()['lang']) || empty($request->getBody()['lang']))
        $errors_arr[] = I18n::getInstance()->translate('API_ADMIN_INVITE_NO_LANG');

    if(count($errors_arr) === 0) {
        $email = Security::protect($request->getBody()['email']);
        $firstname = Security::protect($request->getBody()['firstname']);
        $lastname = Security::protect($request->getBody()['lastname']);
        $role = Security::protect($request->getBody()['role']);
        $lang = Security::protect($request->getBody()['lang']);

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

    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $user = User::getInstance()->getUser($user_id, true);

    if($user === null) {
        $errors_arr[] = "User not found!";
    } else {
        $user_email = isset($request->getBody()['member_email']) ? $request->getBody()['member_email'] : $user['email'];
        $user_password = isset($request->getBody()['member_password']) ? Security::hashPass($request->getBody()['member_password'], Config::HASH_SALT) : $user['password'];
        $user_lastname = isset($request->getBody()['member_lastname']) ? $request->getBody()['member_lastname'] : $user['lastname'];
        $user_firstname = isset($request->getBody()['member_firstname']) ? $request->getBody()['member_firstname'] : $user['firstname'];
        $user_role = isset($request->getBody()['member_role']) ? $request->getBody()['member_role'] : $user['role'];
        $user_banned = isset($request->getBody()['member_banned']) ? $request->getBody()['member_banned'] : $user['banned'];

        $res = User::getInstance()->updateUser($user['id'], $user_email, $user_password, $user_lastname, $user_firstname, $user_role, $user_banned);
        if ($res)
            return json_encode(array("r" => True, "message" => "User successfully updated!"));
        else
            $errors_arr[] = "An error occured while updating the user!";
    }

    return json_encode(array("r" => False, "errors" => $errors_arr));
});
