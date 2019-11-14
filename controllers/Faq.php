<?php

use Testify\Utils\Router\Router;
use Testify\Model\Faq;
use Testify\Model\Role;
use Testify\Utils\Security;
use Testify\Utils\API;

$router = Router::getInstance();


$router->get('/api/faq/questions', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();

    $faq = Faq::getInstance()->getFaq();
    return json_encode(array("r" => True, "faq" => $faq));
});

$router->post('/api/faq/questions', function($request) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if(!isset($request->getBody()['question']) || empty($request->getBody()['question']))
        $errors_arr[] = "Pas de question donnée !";
    if(!isset($request->getBody()['answer']) || empty($request->getBody()['answer']))
        $errors_arr[] = "Pas de réponse donnée !";

    if(count($errors_arr) === 0) {
        $question = Security::protect($request->getBody()['question']);
        $answer = Security::protect($request->getBody()['answer']);

        $question = Faq::getInstance()->createQuestion($question, $answer);
        return json_encode(array("r" => True, "question" => $question));
    } else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});

$router->delete('/api/faq/questions/<question_id>', function($request, $question_id) {
    API::setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if (Faq::getInstance()->deleteQuestion($question_id))
        return json_encode(array("r" => True));
    else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});
