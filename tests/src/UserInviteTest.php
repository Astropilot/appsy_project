<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Database;
use Testify\Model\UserInvite;
use Testify\Component\Security;

class UserInviteTest extends TestCase {

    public function setUp(): void {
        importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_data.sql');
    }

    public function testCreateInvite() {
        $email = 'foo@bar.com';
        $datetime = new \DateTime();
        $now = $datetime->format('Y-m-d H:i:s');
        $token = Security::hashPass($email, $now);
        $datetime->add(new \DateInterval('P3D'));
        $expire_date = $datetime->format('Y-m-d');

        $result = UserInvite::createInvite($email, 'foo', 'bar', 0, $token, $expire_date);

        $this->assertNotSame(FALSE, $result);
    }

    public function testGetValidInvite() {
        $invite = UserInvite::getValidInvite('foo', 'demo4@testify.com');

        $this->assertNotSame(FALSE, $invite);
        $this->assertSame(1, $invite['id']);
        $this->assertSame('demo4@testify.com', $invite['email']);
        $this->assertSame('foo', $invite['firstname']);
        $this->assertSame('bar', $invite['lastname']);
        $this->assertSame(1, $invite['role']);
    }

    public function testUnactiveInvite() {
        $result = UserInvite::unActiveInvite(1);

        $this->assertNotSame(FALSE, $result);
    }
}
