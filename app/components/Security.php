<?php

namespace Testify\Component;

class Security {

    public static function hashPass(string $pass, string $salt) : string {
        return hash("sha256",($salt.$pass));
    }

    public static function protect(string $string) {
        if (ctype_digit($string))
            $string = intval($string);
        return $string;
    }

    public static function isLogged() : bool {
        if (!isset($_SESSION['email']) || !isset($_SESSION['id']))
            return False;
        return True;
    }

    public static function checkAPIConnected() {
      if (!self::isLogged()) {
        echo json_encode(array("r" => False, "errors" => array("Vous devez être connecté !")));
        exit;
      }
    }
}
?>
