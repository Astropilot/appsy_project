<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Role;

class RoleTest extends TestCase {

    public function testIsUser() {
        $_SESSION['role'] = Role::$ROLES['USER'];
        $this->assertSame(TRUE, Role::isUser());

        $_SESSION['role'] = Role::$ROLES['EXAMINATOR'];
        $this->assertSame(FALSE, Role::isUser());

        $_SESSION['role'] = Role::$ROLES['ADMINISTRATOR'];
        $this->assertSame(FALSE, Role::isUser());
    }

    public function testIsAdmin() {
        $_SESSION['role'] = Role::$ROLES['USER'];
        $this->assertSame(FALSE, Role::isAdmin());

        $_SESSION['role'] = Role::$ROLES['EXAMINATOR'];
        $this->assertSame(FALSE, Role::isAdmin());

        $_SESSION['role'] = Role::$ROLES['ADMINISTRATOR'];
        $this->assertSame(TRUE, Role::isAdmin());
    }
}
