<?php

namespace Testify\Component;

class Security {

    public static function hashPass(string $pass, string $salt) : string {
        return hash('sha256', ($salt.$pass));
    }

    public static function protect(string $string) {
        $string_sanitized = $string;
        $pos = strpos($string, '-');
        if ($pos !== false) {
            $string_sanitized = substr_replace($string, '', $pos, 1);
        }
        if (ctype_digit($string_sanitized))
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
        http_response_code(401);
        echo json_encode(array('errors' => array('Vous devez être connecté !')));
        exit;
      }
    }
}
?>
