<?php

include_once 'Database.php';

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
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT * FROM tf_faq ORDER BY `created_at`"
        );
        $req->execute();
        return ($req->fetchAll());
    }

    public function getQuestion($question_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, question, answer, created_at FROM tf_faq WHERE `id`=:qid"
        );
        $req->execute(array(
            'qid'=> $question_id
        ));
        return ($req->fetch());
    }

    public function createQuestion($question, $answer) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_faq SET `question`=:question, `created_at`= NOW(), `answer`=:answer"
        );
        $req->execute(array(
            'question' => $question,
            'answer' => $answer
        ));

        $question_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getQuestion($question_id);
    }

    public function deleteQuestion($question_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "DELETE FROM tf_faq WHERE `id`=:qid"
        );
        return $req->execute(array(
            'qid' => $question_id
        ));
    }
}
