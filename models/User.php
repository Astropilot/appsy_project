<?php

include_once 'Database.php';

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
            "SELECT id, email, lastname, firstname, role FROM tf_user WHERE `id`=:userid"
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
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, email, lastname, firstname, role
            FROM tf_user
            WHERE (tf_user.email LIKE :search1
                    OR
                  tf_user.firstname LIKE :search2
                    OR
                  tf_user.lastname LIKE :search3)" . $exclude_users
        );
        $req->bindValue(':search1', '%' . $search . '%');
        $req->bindValue(':search2', '%' . $search . '%');
        $req->bindValue(':search3', '%' . $search . '%');
        $req->execute();
        return ($req->fetchAll());
    }
}
