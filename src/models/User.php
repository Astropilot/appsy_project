<?php

namespace Testify\Model;

use Testify\Model\Database;

class User {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : User {
        if(is_null(self::$instance)) {
            self::$instance = new User();
        }
        return self::$instance;
    }

    public function userExist($email, $password) : bool {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT 1 FROM tf_user WHERE `email`=:email AND `password`=:pass"
        );
        $req->execute(array(
            'email' => $email,
            'pass' => $password,
        ));
        return ($req->fetchColumn() ? True : False);
    }

    public function getUserID($email) : int {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id FROM tf_user WHERE `email`=:email"
        );
        $req->execute(array(
            'email'=> $email
        ));
        return ($req->fetchColumn());
    }

    public function getUserRole($user_id) : int {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT role FROM tf_user WHERE `id`=:userid"
        );
        $req->execute(array(
            'userid'=> $user_id
        ));
        return ($req->fetchColumn());
    }

    public function getUser($user_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, email, lastname, firstname, role, banned FROM tf_user WHERE `id`=:userid"
        );
        $req->execute(array(
            'userid'=> $user_id
        ));
        return ($req->fetch());
    }

    public function findContacts($search, $include_users) {
        $exclude_users = '';
        if (!$include_users)
            $exclude_users = ' AND `role` > 0';

        $req_sql = "SELECT id, email, lastname, firstname, role, banned
                    FROM tf_user ";
        if (strlen($search) > 0) {
            $req_sql .= "WHERE (tf_user.email LIKE :search1
                                    OR
                                tf_user.firstname LIKE :search2
                                    OR
                                tf_user.lastname LIKE :search3)";
        } else {
            $req_sql .= "WHERE 1=1 ";
        }
        $req_sql .= $exclude_users;

        $req = Database::getInstance()->getPDO()->prepare($req_sql);
        $req->bindValue(':search1', '%' . $search . '%');
        $req->bindValue(':search2', '%' . $search . '%');
        $req->bindValue(':search3', '%' . $search . '%');
        $req->execute();
        return ($req->fetchAll());
    }

    public function createInvite($email, $firstname, $lastname, $role, $token, $expire_date): bool {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_user_invited
             SET `email`=:email, `firstname`=:firstname, `lastname`=:lastname,
             `role`=:role, `invite_token`=:token, `expire_date`=:expire"
        );
        return $req->execute(array(
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'role' => $role,
            'token' => $token,
            'expire' => $expire_date
        ));
    }
}
