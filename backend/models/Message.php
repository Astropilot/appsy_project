<?php

include_once 'Database.php';
include_once 'User.php';

class Message {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : Message {
        if(is_null(self::$instance)) {
            self::$instance = new Message();
        }
        return self::$instance;
    }

    public function getUserMessages($user) {
        $messages = array();
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, author, message, created_at
             FROM tf_message
             WHERE author = :userid
             ORDER BY `created_at` DESC"
        );
        $req->execute(array(
            'userid' => $user['id']
        ));

        while ($row = $req->fetch()) {
            $row['author'] = $user;
            array_push($messages, $row);
        }

        return ($messages);
    }

    public function getMessage($message_id, $user=NULL) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, author, message, created_at
            FROM tf_message
            WHERE `id`=:mid"
        );
        $req->execute(array(
            'mid'=> $message_id
        ));
        $message = $req->fetch();
        if ($user !== NULL)
            $message['author'] = $user;
        else
            $message['author'] = User::getInstance()->getUser($message['author']);

        return ($message);
    }

    public function createMessage($user, $message) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_message SET `author`=:userid, `message`=:message, `created_at`= NOW()"
        );
        $req->execute(array(
            'userid' => $user['id'],
            'message' => $message
        ));

        $message_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getMessage($message_id, $user);
    }
}
