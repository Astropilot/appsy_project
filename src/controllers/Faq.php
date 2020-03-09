<?php

use Testify\Router\Router;
use Testify\Router\Response;
use Testify\Model\Faq;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;

$router = Router::getInstance();


$router->get('/api/faq/questions', function($request) {
    API::setAPIHeaders();

    $faq = Faq::getFaq();
    if ($faq === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FAQ_GET_FAQ_ERROR'), 500);
    return new Response(
        json_encode(array('faq' => $faq))
    );
});

$router->post('/api/faq/questions', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getData();

    if(!$data->existAndNotEmpty('question'))
        $errors_arr[] = I18n::getInstance()->translate('API_FAQ_NO_QUESTION_GIVEN');
    if(!$data->existAndNotEmpty('answer'))
        $errors_arr[] = I18n::getInstance()->translate('API_FAQ_NO_ANSWER_GIVEN');

    if(count($errors_arr) > 0) {
        return API::makeResponseError($errors_arr, 400);
    }

    $question = $data->get('question');
    $answer = $data->get('answer');

    $question = Faq::createQuestion($question, $answer);
    if($question === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FAQ_CREATE_FAQ_ERROR'), 500);

    return new Response(
        json_encode(array('question' => $question)),
        201
    );
});

$router->delete('/api/faq/questions/<question_id:int>', function($request, $question_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    if (Faq::deleteQuestion($question_id) === FALSE)
        return API::makeResponseError(I18n::getInstance()->translate('API_FAQ_DELETE_FAQ_ERROR'), 500);

    return new Response(
        '',
        204
    );
});
