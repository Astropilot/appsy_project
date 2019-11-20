<?php

use Testify\Router\Router;
use Testify\Model\Faq;
use Testify\Model\Role;
use Testify\Component\Security;
use Testify\Component\API;
use Testify\Component\I18n;

$router = Router::getInstance();


$router->get('/api/faq/questions', function($request) {
    API::setAPIHeaders();

    $faq = Faq::getInstance()->getFaq();
    return json_encode(array("r" => True, "faq" => $faq));
});

$router->post('/api/faq/questions', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();
    $data = $request->getBody();

    if(!isset($data['question']) || empty($data['question']))
        $errors_arr[] = I18n::getInstance()->translate('API_FAQ_NO_QUESTION_GIVEN');
    if(!isset($data['answer']) || empty($data['answer']))
        $errors_arr[] = I18n::getInstance()->translate('API_FAQ_NO_ANSWER_GIVEN');

    if(count($errors_arr) === 0) {
        $question = $data['question'];
        $answer = $data['answer'];

        $question = Faq::getInstance()->createQuestion($question, $answer);
        return json_encode(array("r" => True, "question" => $question));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->delete('/api/faq/questions/<question_id:int>', function($request, $question_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if (Faq::getInstance()->deleteQuestion($question_id))
        return json_encode(array("r" => True));
    else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});
