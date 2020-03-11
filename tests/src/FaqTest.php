<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Model\Database;
use Testify\Model\Faq;

class FaqTest extends TestCase {

    public function setUp(): void {
        importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_data.sql');
    }

    public function testGettingFaq() {
        $faq = Faq::getFaq();

        $this->assertCount(3, $faq);
        $this->assertSame('Comment s\'inscrire ?', $faq[1]['question']);
    }

    public function testGetQuestion() {
        $question = Faq::getQuestion(1);

        $this->assertNotSame(FALSE, $question);
        $this->assertSame(1, $question['id']);
        $this->assertSame('Je n\'arrive pas à me connecter.', $question['question']);
        $this->assertSame('La reponse c\'est la vie', $question['answer']);

        $question = Faq::getQuestion(8);

        $this->assertSame(FALSE, $question);
    }

    public function testCreateQuestion() {
        $question = Faq::createQuestion('Hello', 'World');

        $this->assertNotSame(FALSE, $question);
        $this->assertSame('Hello', $question['question']);
        $this->assertSame('World', $question['answer']);
    }

    public function testDeleteQuestion() {
        $result = Faq::deleteQuestion(1);

        $this->assertNotSame(FALSE, $result);
    }
}
