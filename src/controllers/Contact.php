<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Component\API;
use Testify\Component\I18n;
use Testify\Component\Mail;

use \Testify\Config;

$router = Router::getInstance();
$router->post('/api/contact', function($request) {
    $errors_arr = array();
    $data = $request->getBody();

    API::setAPIHeaders();

    if(!isset($data['name']) || empty($data['name']))
        $errors_arr[] = "No name given!";
    if(!isset($data['email']) || empty($data['email']))
        $errors_arr[] = "No email given!";
    if(!isset($data['message']) || empty($data['message']))
        $errors_arr[] = "No message given!";

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $name = $data['name'];
    $email = $data['email'];
    $message = $data['message'];

    $context = array(
        'user' => $name,
        'message' => $message,
        'email' => $email
    );

    $mail = new Mail(
        array(
            'smtp_host' => getenv('SMTP_HOST'),
            'username' => getenv('MAIL_USERNAME'),
            'password' => getenv('MAIL_PASSWORD'),
            'name' => 'Testify'
        ),
        getenv('MAIL_USERNAME'),
        I18n::getInstance()->translate('API_CONTACT_MAIL_TITLE', 'fr'),
        Response::fromView('mails/contact.html', $context, 'fr')->getContent()
    );

    if ($mail->sendMail()) {
        return new Response(
            json_encode(array("message" => "Votre message à bien été envoyé !")),
            201
        );
    } else {
        return API::makeResponseError("Une erreur est survenue pendant l'envoi de votre message", 500);
    }
});
