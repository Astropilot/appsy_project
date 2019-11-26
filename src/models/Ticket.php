<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Model\User;

class Ticket {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : Ticket {
        if(is_null(self::$instance)) {
            self::$instance = new Ticket();
        }
        return self::$instance;
    }

    public function getTicketsFromUser($user): array {
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
    }

    public function getTicket($ticket_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT * FROM tf_ticket WHERE `id`=:id"
        );

        $req->execute(array(
            'id' => $ticket_id
        ));
        return ($req->fetch());
    }

    public function getTicketComments($ticket) : array {
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
            array_push($comments, $row);
        }

        return ($comments);
    }
}
