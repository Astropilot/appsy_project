<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Utils\API;

class ApiTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testInArrayKey() {
        API::setAPIHeaders();

        $headers = xdebug_get_headers();

        $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        $this->assertContains('Content-Type: application/json; charset=UTF-8', $headers);
        $this->assertContains('Access-Control-Allow-Methods: GET, POST, PUT, DELETE', $headers);
        $this->assertContains('Access-Control-Max-Age: 3600', $headers);
        $this->assertContains('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With', $headers);
    }
}
