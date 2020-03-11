<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Database;
use Testify\Model\Faq;

class TestTest extends TestCase {

    public function setUp(): void {
        importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_data.sql');
    }

    public function testPaginatePage1() {
        $question = Faq::createQuestion('test', 'lol');

        $faq = Faq::getFaq();

        $this->assertCount(1, $faq);
    }
}
