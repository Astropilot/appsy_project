<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Config;

class User {

    private function __construct() {}

    public static function createUser($email, $firstname, $lastname, $role, $password) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "INSERT INTO tf_user
                 (email, firstname, lastname, role, password, banned)
                 VALUES (:email, :firstname, :lastname, :role, :password, 0)"
            );
            return $req->execute(array(
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'role' => $role,
                'password' => $password
            ));
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function deleteUser($user_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "DELETE FROM tf_user WHERE `id`=:uid"
            );
            return $req->execute(array(
                'uid' => $user_id
            ));
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function userExist($email, $password) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT 1 FROM tf_user WHERE `email`=:email AND `password`=:pass AND `banned`=0"
            );
            $req->execute(array(
                'email' => $email,
                'pass' => $password,
            ));
            return ($req->fetchColumn() ? True : False);
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getUserID($email) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT id FROM tf_user WHERE `email`=:email"
            );
            $req->execute(array(
                'email'=> $email
            ));
            return ($req->fetchColumn());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getUserRole($user_id) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT role FROM tf_user WHERE `id`=:userid"
            );
            $req->execute(array(
                'userid'=> $user_id
            ));
            return ($req->fetchColumn());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function getUser($user_id, $with_password=false) {
        $password_field = '';
        if ($with_password)
            $password_field = 'password,';

        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "SELECT id, email, lastname, $password_field firstname, role, banned FROM tf_user WHERE `id`=:userid"
            );
            $req->execute(array(
                'userid'=> $user_id
            ));
            return ($req->fetch());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function findContacts($search, $include_users) {
        $exclude_users = '';
        if (!$include_users)
            $exclude_users = " AND `role`>0";

        $req_sql = "SELECT id, email, lastname, firstname, role, banned
                    FROM tf_user ";
        if (strlen($search) > 0) {
            $req_sql .= "WHERE (tf_user.email LIKE :search1
                                    OR
                                tf_user.firstname LIKE :search2
                                    OR
                                tf_user.lastname LIKE :search3)";
        } else {
            $req_sql .= "WHERE 1=1";
        }
        $req_sql .= $exclude_users;

        try {
            $req = Database::getInstance()->getPDO()->prepare($req_sql);
            $req->bindValue(':search1', '%' . $search . '%');
            $req->bindValue(':search2', '%' . $search . '%');
            $req->bindValue(':search3', '%' . $search . '%');
            $req->execute();
            return ($req->fetchAll());
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }

    public static function updateUser($userid, $email, $password, $lastname, $firstname, $role, $banned) {
        try {
            $req = Database::getInstance()->getPDO()->prepare(
                "UPDATE tf_user
                 SET `email`=:email, `password`=:password, `lastname`=:lastname, `firstname`=:firstname, `role`=:role, `banned`=:banned
                 WHERE `id`=:uid"
            );
            return $req->execute(array(
                'uid' => $userid,
                'email' => $email,
                'password' => $password,
                'lastname' => $lastname,
                'firstname' => $firstname,
                'role' => $role,
                'banned' => $banned
            ));
        } catch (\PDOException $e) {
            Database::throwIfDeveloppment($e, Config::ENVIRONNEMENT);
            return FALSE;
        }
    }
}
