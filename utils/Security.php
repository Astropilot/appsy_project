<?php

error_reporting(-1);

class Security {

    public static function hasPass(string $pass) : string {
        $salt = "a3t=Xc7G?xyUR!YP";
        return hash("sha256",($salt.$pass));
    }

    public static function protect(string $string) {
        if (ctype_digit($string))
            $string = intval($string);
        else
            $string = addcslashes($string, '%_');
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
