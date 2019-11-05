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

    public function getUserContactMessages($user, $contact) {
        $messages = array();
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, author, recipient, message, created_at
             FROM tf_message
             WHERE
                    (author = :userid AND recipient = :contactid)
                OR
                    (author = :contactid2 AND recipient = :userid2)
             ORDER BY `created_at` DESC"
        );
        $req->execute(array(
            'userid' => $user['id'],
            'userid2' => $user['id'],
            'contactid' => $contact['id'],
            'contactid2' => $contact['id']
        ));

        while ($row = $req->fetch()) {
            if ($row['author'] === $user['id']) {
                $row['author'] = $user;
                $row['recipient'] = $contact;
            } else {
                $row['author'] = $contact;
                $row['recipient'] = $user;
            }
            array_push($messages, $row);
        }

        return ($messages);
    }

    public function getContacts($user) {
        $contacts_id = array();
        $contacts = array();

        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT DISTINCT author, recipient
             FROM tf_message
             WHERE author = :userid OR recipient = :userid2
             ORDER BY `created_at` DESC"
        );
        $req->execute(array(
            'userid' => $user['id'],
            'userid2' => $user['id']
        ));

        while ($row = $req->fetch()) {
            if ($row['author'] === $user['id'] && !in_array($row['recipient'], $contacts_id))
                array_push($contacts_id, $row['recipient']);
            else if ($row['recipient'] === $user['id'] && !in_array($row['author'], $contacts_id))
                array_push($contacts_id, $row['author']);
        }

        foreach ($contacts_id as $contact_id){
            array_push($contacts, User::getInstance()->getUser($contact_id));
        }

        return $contacts;
    }

    public function getMessage($message_id, $user, $contact) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, author, recipient, message, created_at
             FROM tf_message
             WHERE `id`=:mid"
        );
        $req->execute(array(
            'mid'=> $message_id
        ));
        $message = $req->fetch();

        $message['author'] = $user;
        $message['recipient'] = $contact;

        return ($message);
    }

    public function createMessage($user, $contact, $message) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_message
             SET `author`=:userid, `recipient`=:contactid, `message`=:message, `created_at`= NOW()"
        );
        $req->execute(array(
            'userid' => $user['id'],
            'contactid' => $contact['id'],
            'message' => $message
        ));

        $message_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getMessage($message_id, $user, $contact);
    }
}
