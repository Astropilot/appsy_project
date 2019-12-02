<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Model\User;
use Testify\Config;

class Ticket {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : Ticket {
        if(is_null(self::$instance)) {
            self::$instance = new Ticket();
        }
        return self::$instance;
    }

    public function getTicketsFromUser($user) {
        try {
            $tickets = array();
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT *
                 FROM tf_ticket
                 WHERE author = :userid
                 ORDER BY `updated_at` DESC"
            );
            $req->execute(array(
                'userid' => $user['id'],
            ));

            while ($row = $req->fetch()) {
                $row['author'] = $user;
                array_push($tickets, $row);
            }

            return ($tickets);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function getTicket($ticket_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT * FROM tf_ticket WHERE `id`=:id"
            );

            $req->execute(array(
                'id' => $ticket_id
            ));
            $ticket = $req->fetch();
            if($ticket)
                $ticket['content'] = html_entity_decode($ticket['content']);
            return ($ticket);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function getTicketComments($ticket) {
        try {
            $comments = array();
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT * FROM tf_ticket_comment WHERE `ticket`=:id"
            );
            $req->execute(array(
                'id' => $ticket['id']
            ));

            while ($row = $req->fetch()) {
                $row['ticket'] = $ticket;
                $row['author'] = User::getInstance()->getUser($row['author']);
                $row['content'] = html_entity_decode($row['content']);
                array_push($comments, $row);
            }

            return ($comments);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public function createTicket($author, $title, $content, $status) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "INSERT INTO tf_ticket
                 SET `author`=:author, `title`=:title, `content`=:content, `status`=:status, `updated_at`=NOW()"
            );
            $req->execute(array(
                'author' => $author,
                'title' => $title,
                'content' => $content,
                "status" => $status
            ));

            $ticket_id = Database::getInstance()->getPDO()->lastInsertId();
            return self::getTicket($ticket_id);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }
}
