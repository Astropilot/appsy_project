<?php

namespace Testify\Model;

use Testify\Model\Database;

class UserInvite {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : UserInvite {
        if(is_null(self::$instance)) {
            self::$instance = new UserInvite();
        }
        return self::$instance;
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

    public function getValidInvite($token, $email) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, email, firstname, lastname, role
             FROM tf_user_invited
             WHERE `email`=:email AND `invite_token`=:token AND `expire_date`>=NOW() AND `active`=1
             LIMIT 1"
        );
        $req->execute(array(
            'email' => $email,
            'token' => $token
        ));
        return ($req->fetch());
    }

    public function unActiveInvite($invite_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "UPDATE tf_user_invited
             SET `active`=0
             WHERE `id`=:id"
        );
        $req->execute(array(
            'id' => $invite_id,
        ));
    }
}
