<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Component\API;
use Testify\Router\Response;

class APITest extends TestCase {

    /*public function testAPIHeaders() {
        API::setAPIHeaders();

        $headers = xdebug_get_headers();

        $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        $this->assertContains('Content-Type: application/json; charset=UTF-8', $headers);
        $this->assertContains('Access-Control-Allow-Methods: GET, POST, PUT, DELETE', $headers);
        $this->assertContains('Access-Control-Max-Age: 3600', $headers);
        $this->assertContains('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With', $headers);
    }*/

    public function testSimpleResponseError() {
        $response = API::makeResponseError('Resource not found!', 404);

        $this->assertSame('{"errors":["Resource not found!"]}', $response->getContent());
        $this->assertSame(404, $response->getHttpCode());

        $response = API::makeResponseError('An error occured!', 500);

        $this->assertSame('{"errors":["An error occured!"]}', $response->getContent());
        $this->assertSame(500, $response->getHttpCode());
    }
}
