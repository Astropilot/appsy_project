<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Component\Security;

class SecurityTest extends TestCase {

    public function testHashPass() {
        $this->assertSame(
            '4e569b0569294ed18f8abd8d192dac45ed2e1c7bfb3c6bd54adecfa31385baaa',
            Security::hashPass('Foo')
        );
        $this->assertSame(
            '98f820c51b1096a3ad9d0196932cee41fe4c06574a88a0fd3557c3992e22b77a',
            Security::hashPass('Bar')
        );
    }

    public function testProtect() {
        $this->assertSame(
            'Foo',
            Security::protect('Foo')
        );
        $this->assertSame(
            '\%Test',
            Security::protect('%Test')
        );
        $this->assertSame(
            'Un\_Test',
            Security::protect('Un_Test')
        );
    }

    public function testisLogged() {
        $this->assertSame(
            False,
            Security::isLogged()
        );

        $_SESSION['email'] = 'something';
        $_SESSION['id'] = 'something';

        $this->assertSame(
            True,
            Security::isLogged()
        );
    }
}
