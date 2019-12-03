<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Config;

class Faq {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : Faq {
        if(is_null(self::$instance)) {
            self::$instance = new Faq();
        }
        return self::$instance;
    }

    public function getFaq() {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT * FROM tf_faq ORDER BY `created_at`"
            );
            $req->execute();
            return ($req->fetchAll());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function getQuestion($question_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT id, question, answer, created_at FROM tf_faq WHERE `id`=:qid"
            );
            $req->execute(array(
                'qid'=> $question_id
            ));
            return ($req->fetch());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function createQuestion($question, $answer) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "INSERT INTO tf_faq SET `question`=:question, `created_at`= NOW(), `answer`=:answer"
            );
            $req->execute(array(
                'question' => $question,
                'answer' => $answer
            ));

            $question_id = Database::getInstance()->getPDO()->lastInsertId();
            return self::getQuestion($question_id);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function deleteQuestion($question_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "DELETE FROM tf_faq WHERE `id`=:qid"
            );
            return $req->execute(array(
                'qid' => $question_id
            ));
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }
}
