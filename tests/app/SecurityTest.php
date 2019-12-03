<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Component\Security;

class SecurityTest extends TestCase {

    public function testHashPass() {
        $this->assertSame(
            '4e569b0569294ed18f8abd8d192dac45ed2e1c7bfb3c6bd54adecfa31385baaa',
            Security::hashPass('Foo', 'a3t=Xc7G?xyUR!YP')
        );
        $this->assertSame(
            '98f820c51b1096a3ad9d0196932cee41fe4c06574a88a0fd3557c3992e22b77a',
            Security::hashPass('Bar', 'a3t=Xc7G?xyUR!YP')
        );
    }

    public function testProtect() {
        $this->assertSame(
            'Foo',
            Security::protect('Foo')
        );
        $this->assertSame(
            '\%Test',
            Security::protect('\%Test')
        );
        $this->assertSame(
            5,
            Security::protect('5')
        );
        $this->assertSame(
            -58,
            Security::protect('-58')
        );
    }

    public function testisLogged() {
        $this->assertSame(
            False,
            Security::isLogged()
        );

        $_SESSION['email'] = 'something';
        $_SESSION['id'] = '1';

        $this->assertSame(
            True,
            Security::isLogged()
        );
    }
}
