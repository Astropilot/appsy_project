<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Database;
use Testify\Model\Message;

class MessageTest extends TestCase {

    public function setUp(): void {
        importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_data.sql');
    }

    public function testGetUserContactMessages() {
        $messages = Message::getUserContactMessages(
            array('id' => 1),
            array('id' => 2)
        );

        $this->assertNotSame(FALSE, $messages);
        $this->assertCount(2, $messages);
    }

    public function testGetContacts() {
        $contacts = Message::getContacts(array('id' => 1));

        $this->assertNotSame(FALSE, $contacts);
        $this->assertCount(1, $contacts);
        $this->assertSame(2, $contacts[0]['user']['id']);
    }

    public function testGetMessage() {
        $message = Message::getMessage(
            1,
            array('id' => 1),
            array('id' => 2)
        );

        $this->assertNotSame(FALSE, $message);
        $this->assertSame(1, $message['id']);
        $this->assertSame(1, $message['author']['id']);
        $this->assertSame(2, $message['recipient']['id']);
    }

    public function testCreateMessage() {
        $message = Message::createMessage(
            array('id' => 1),
            array('id' => 2),
            'Foobar'
        );

        $this->assertNotSame(FALSE, $message);
        $this->assertSame(1, $message['author']['id']);
        $this->assertSame(2, $message['recipient']['id']);
    }
}
