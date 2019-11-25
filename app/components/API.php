<?php

namespace Testify\Component;

use Testify\Router\Response;

class API {

    public static function setAPIHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public static function makeResponseError($error, $httpCode) {
        if (!is_array($error))
            $error = array($error);
        return new Response(
            json_encode(array("errors" => $error)),
            $httpCode
        );
    }
}
