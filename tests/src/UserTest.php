<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Database;
use Testify\Model\User;
use Testify\Component\Security;

class UserTest extends TestCase {

    public function setUp(): void {
        importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_data.sql');
    }

    public function testGetUserID() {
        $id = User::getUserID('demo@testify.com');

        $this->assertSame(1, $id);
    }

    public function testGetUserRole() {
        $role = User::getUserRole(1);

        $this->assertSame(2, $role);
    }

    public function testGetUser() {
        $user = User::getUser(1, false);

        $this->assertNotSame(FALSE, $user);
        $this->assertSame(1, $user['id']);
        $this->assertSame('demo@testify.com', $user['email']);
        $this->assertSame('John', $user['firstname']);
        $this->assertSame('Doe', $user['lastname']);
        $this->assertSame(2, $user['role']);
        $this->assertSame(0, $user['banned']);

        $user = User::getUser(1, true);

        $this->assertNotSame(FALSE, $user);
        $this->assertSame(1, $user['id']);
        $this->assertSame('demo@testify.com', $user['email']);
        $this->assertSame('John', $user['firstname']);
        $this->assertSame('Doe', $user['lastname']);
        $this->assertSame('A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', $user['password']);
        $this->assertSame(2, $user['role']);
        $this->assertSame(0, $user['banned']);
    }

    public function testCreateUser() {
        $result = User::createUser(
            'demo4@testify.com',
            'Joe',
            'Doe',
            0,
            Security::hashPass('test', 'a3t=Xc7G?xyUR!YP')
        );

        $this->assertNotSame(FALSE, $result);
    }

    public function testDeleteUser() {
        $result = User::deleteUser(1);

        $this->assertNotSame(FALSE, $result);
    }

    public function testUserExist() {
        $user1 = User::userExist('demo@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C');
        $user2 = User::userExist('notexisting@testify.com', 'foo');

        $this->assertNotSame(FALSE, $user1);
        $this->assertSame(FALSE, $user2);
    }

    public function testFindContacts() {
        $users = User::findContacts('demo', true);
        $users2 = User::findContacts('demo', false);

        $this->assertCount(3, $users);
        $this->assertCount(2, $users2);
    }

    public function testUpdateUser() {
        $res = User::updateUser(1, 'demo7@testify.com', 'foo', 'bar', 'foo', 1, 1);

        $this->assertNotSame(FALSE, $res);
    }
}
