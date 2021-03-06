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
    $data = $request->getData();

    API::setAPIHeaders();

    if(!$data->existAndNotEmpty('name'))
        $errors_arr[] = I18n::getInstance()->translate('API_CONTACT_NO_NAME');
    if(!$data->existAndNotEmpty('email'))
        $errors_arr[] = I18n::getInstance()->translate('API_CONTACT_NO_EMAIL');
    if(!$data->existAndNotEmpty('message'))
        $errors_arr[] = I18n::getInstance()->translate('API_CONTACT_NO_MESSAGE');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $name = $data->get('name');
    $email = $data->get('email');
    $message = $data->get('message');

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

    if ($mail->sendMail() === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_CONTACT_ERROR'), 500);

    return new Response(
        json_encode(array("message" => I18n::getInstance()->translate('API_CONTACT_SUCCESS'))),
        201
    );
});
