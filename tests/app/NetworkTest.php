<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Network\Request;

class NetworkTest extends TestCase {

    public function testGetRequest() {
        $req = new Request('http://jsonplaceholder.typicode.com/todos/1');

        $response = $req->get();

        $this->assertSame(
            '{"userId":1,"id":1,"title":"delectusautautem","completed":false}',
            preg_replace('/\s+/', '', $response)
        );
    }

    public function testSSLGetRequest() {
        $req = new Request('https://jsonplaceholder.typicode.com/todos/1');

        $response = $req->get();

        $this->assertSame(
            '{"userId":1,"id":1,"title":"delectusautautem","completed":false}',
            preg_replace('/\s+/', '', $response)
        );
    }

    public function testJsonPostRequest() {
        $req = new Request('http://jsonplaceholder.typicode.com/posts');

        $response = $req->post(json_encode(array(
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        )), 'json');

        $this->assertSame(
            '{"title":"foo","body":"bar","userId":1,"id":101}',
            preg_replace('/\s+/', '', $response)
        );
    }

    public function testSSLJsonPostRequest() {
        $req = new Request('https://jsonplaceholder.typicode.com/posts');

        $response = $req->post(json_encode(array(
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        )), 'json');

        $this->assertSame(
            '{"title":"foo","body":"bar","userId":1,"id":101}',
            preg_replace('/\s+/', '', $response)
        );
    }

    public function testFormPostRequest() {
        $req = new Request('http://appsyproject.free.beeceptor.com/testpostform');

        $response = $req->post(array(
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ));

        $this->assertSame(
            '{"title":"foo","body":"bar","userId":1,"id":101}',
            preg_replace('/\s+/', '', $response)
        );
    }

    public function testSSLFormPostRequest() {
        $req = new Request('https://appsyproject.free.beeceptor.com/testpostform');

        $response = $req->post(array(
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ));

        $this->assertSame(
            '{"title":"foo","body":"bar","userId":1,"id":101}',
            preg_replace('/\s+/', '', $response)
        );
    }

    /*public function testMixedRequest() {
        $req = new Request('http://appsyproject.free.beeceptor.com/textmixed');

        $response = $req->get();

        $this->assertSame(
            '{"status":"Awesome!"}',
            preg_replace('/\s+/', '', $response)
        );

        $response = $req->post(array('foo' => 'bar'));

        $this->assertSame(
            '{"status":"Hello!"}',
            preg_replace('/\s+/', '', $response)
        );
    }*/
}
