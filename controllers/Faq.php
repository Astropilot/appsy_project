<?php

include_once 'Configuration.php';
include_once 'models/Faq.php';
include_once 'models/Role.php';
include_once 'utils/Security.php';
include_once 'utils/API.php';


$router = Router::getInstance();


$router->get(TESTIFY_API_ROOT . 'faq/questions', function($request) {
    setAPIHeaders();
    Security::checkAPIConnected();

    $faq = Faq::getInstance()->getFaq();
    return json_encode(array("r" => True, "faq" => $faq));
});

$router->post(TESTIFY_API_ROOT . 'faq/questions', function($request) {
    setAPIHeaders();
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

$router->delete(TESTIFY_API_ROOT . 'faq/questions/<question_id>', function($request, $question_id) {
    setAPIHeaders();
    Security::checkAPIConnected();
    Role::checkPermissions(Role::$ROLES['ADMINISTRATOR']);

    $errors_arr=array();

    if (Faq::getInstance()->deleteQuestion($question_id))
        return json_encode(array("r" => True));
    else
        return json_encode(array("r" => False, "errors" => $errors_arr));
});
