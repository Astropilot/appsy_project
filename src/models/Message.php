<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Model\User;
use Testify\Component\Arrays;
use Testify\Config;

class Message {

    private function __construct() {}

    public static function getUserContactMessages($user, $contact) {
        try {
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
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getContacts($user) {
        try {
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
                    'user' => User::getUser($contact_id['id']),
                    'message' => $contact_id['message'],
                    'created_at' => $contact_id['created_at']
                );
                array_push($contacts, $contact);
            }

            return $contacts;
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getMessage($message_id, $user, $contact) {
        try {
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
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function createMessage($user, $contact, $message) {
        try {
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
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }
}
