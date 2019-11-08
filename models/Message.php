<?php

include_once 'Database.php';
include_once 'User.php';

include_once 'utils/Arrays.php';

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
            "SELECT author, recipient, message, created_at
             FROM tf_message
             WHERE author = :userid OR recipient = :userid2
             ORDER BY `created_at` DESC"
        );
        $req->execute(array(
            'userid' => $user['id'],
            'userid2' => $user['id']
        ));

        while ($row = $req->fetch()) {
            if ($row['author'] === $user['id'] && !Arrays::in_array_key($contacts_id, 'id', $row['recipient']))
                array_push(
                    $contacts_id,
                    array(
                        'id' => $row['recipient'],
                        'message' => $row['message'],
                        'created_at' => $row['created_at']
                    )
                );
            else if ($row['recipient'] === $user['id'] && !Arrays::in_array_key($contacts_id, 'id', $row['author']))
            array_push(
                $contacts_id,
                array(
                    'id' => $row['author'],
                    'message' => $row['message'],
                    'created_at' => $row['created_at']
                )
            );
        }

        foreach ($contacts_id as $contact_id) {
            $contact = array(
                'user' => User::getInstance()->getUser($contact_id['id']),
                'message' => $contact_id['message'],
                'created_at' => $contact_id['created_at']
            );
            array_push($contacts, $contact);
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
