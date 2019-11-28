<?php

namespace Testify\Router;

use Testify\Router\IRequest;
use Testify\Component\Security;


class Request implements IRequest {

    function __construct() {
        $this->bootstrapSelf();
    }

    private function bootstrapSelf() {
        $this->serverProtocol = $_SERVER['SERVER_PROTOCOL'];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestUri = $_SERVER['REQUEST_URI'];
    }

    public function getBody() {
        $body = array();
        if($this->requestMethod === 'GET') {
            foreach($_GET as $key => $value) {
                $body[$key] = Security::protect(filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS));
            }
        }
        if ($this->requestMethod === 'POST') {
            foreach($_POST as $key => $value) {
                $body[$key] = Security::protect(filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS));
            }
        }
        if ($this->requestMethod === 'PUT') {
            parse_str(file_get_contents('php://input'), $body);
            $body = array_map(function($e) {
                return Security::protect($e);
            }, $body);
        }
        return $body;
    }
}
