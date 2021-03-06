<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Model\User;
use Testify\Config;

class Ticket {

    private function __construct() {}

    public static function getTicketsFromUser($user) {
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
                $row['content'] = html_entity_decode($row['content']);
                array_push($tickets, $row);
            }

            return ($tickets);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getTicket($ticket_id) {
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

    public static function getTicketComments($ticket) {
        try {
            $comments = array();
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT * FROM tf_ticket_comment
                 WHERE `ticket`=:id
                 ORDER BY created_at DESC"
            );
            $req->execute(array(
                'id' => $ticket['id']
            ));

            while ($row = $req->fetch()) {
                $row['ticket'] = $ticket;
                $row['author'] = User::getUser($row['author']);
                $row['content'] = html_entity_decode($row['content']);
                array_push($comments, $row);
            }

            return ($comments);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function createTicket($author, $title, $content, $status) {
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

    public static function findTickets($search) {
        try {
            $tickets = array();
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT tf_ticket.* FROM tf_ticket
                 INNER JOIN tf_user
                 ON tf_ticket.author = tf_user.id
                 WHERE (tf_user.firstname LIKE :search1
                            OR
                        tf_user.lastname LIKE :search2
                            OR
                        tf_ticket.title LIKE :search3
                            OR
                        tf_ticket.content LIKE :search4
                        )
                 ORDER BY tf_ticket.status ASC, tf_ticket.updated_at ASC"
            );
            $req->bindValue(':search1', '%' . $search . '%');
            $req->bindValue(':search2', '%' . $search . '%');
            $req->bindValue(':search3', '%' . $search . '%');
            $req->bindValue(':search4', '%' . $search . '%');
            $req->execute();

            while ($row = $req->fetch()) {
                $row['content'] = html_entity_decode($row['content']);
                $row['author'] = User::getUser($row['author']);
                array_push($tickets, $row);
            }

            return ($tickets);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getTicketComment($comment_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT * FROM tf_ticket_comment WHERE `id`=:id"
            );

            $req->execute(array(
                'id' => $comment_id
            ));
            $comment = $req->fetch();
            if($comment)
                $comment['content'] = html_entity_decode($comment['content']);
            return ($comment);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function createTicketComment($ticket_id, $author, $content) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "INSERT INTO tf_ticket_comment
                 SET `author`=:author, `ticket`=:ticket, `content`=:content"
            );
            $req->execute(array(
                'author' => $author,
                'ticket' => $ticket_id,
                'content' => $content
            ));

            $comment_id = Database::getInstance()->getPDO()->lastInsertId();
            return self::getTicketComment($comment_id);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function updateTicketStatus($ticket_id, $status) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "UPDATE tf_ticket
                 SET `status`=:status
                 WHERE `id`=:id"
            );
            return $req->execute(array(
                'id' => $ticket_id,
                'status' => $status
            ));
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }
}
