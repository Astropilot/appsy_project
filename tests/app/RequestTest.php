<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Router\Request;

class RequestTest extends TestCase {

    public function testGetRequest() {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/sample/page';

        $_GET = array();
        $_GET['page'] = '1';
        $_GET['pageSize'] = '10';
        $_GET['email'] = 'email@example.com';

        $request = new Request();

        $this->assertSame('HTTP/1.0', $request->serverProtocol);
        $this->assertSame('GET', $request->requestMethod);
        $this->assertSame('/sample/page', $request->requestUri);
    }
}
