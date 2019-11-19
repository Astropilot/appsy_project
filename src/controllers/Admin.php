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

$router = Router::getInstance();


$router->post('/admin/api/users', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if(!isset($request->getBody()['email']) || empty($request->getBody()['email']))
        $errors_arr[] = "No email given!";
    if(!isset($request->getBody()['firstname']) || empty($request->getBody()['firstname']))
        $errors_arr[] = "No firstname given!";
    if(!isset($request->getBody()['lastname']) || empty($request->getBody()['lastname']))
        $errors_arr[] = "No lastname given!";
    if(!isset($request->getBody()['role']) || empty($request->getBody()['role']))
        $errors_arr[] = "No role given!";

    if(count($errors_arr) === 0) {
        $email = Security::protect($request->getBody()['email']);
        $firstname = Security::protect($request->getBody()['firstname']);
        $lastname = Security::protect($request->getBody()['lastname']);
        $role = Security::protect($request->getBody()['role']);

        date_default_timezone_set('UTC');

        $datetime = new \DateTime();
        $now = $datetime->format('Y-m-d H:i:s');
        $token = Security::hashPass($email, $now);
        $datetime->add(new \DateInterval('P3D'));
        $expire_date = $datetime->format('Y-m-d');

        $res = UserInvite::getInstance()->createInvite($email, $firstname, $lastname, $role, $token, $expire_date);

        if (!$res)
            $errors_arr[] = "Une erreur est arrivée pendant la création de l'invitation !";
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
                "Testify - Inscription",
                Response::fromView('mails/invite.html', $context)
            );

            if ($mail->sendMail())
                return json_encode(array("r" => True, "message" => "$firstname à bien été invité !"));
            else
                $errors_arr[] = "An error occured while sending email invite!";
        }
    }
    return json_encode(array("r" => False, "errors" => $errors_arr));
});
