<?php

class Role {

    public static $ROLES = array('USER' => 0, 'EXAMINATOR' => 1, 'ADMINISTRATOR' => 2);

    public static function checkPermissions($role) {
        if (intval($_SESSION['role']) < $role) {
            echo json_encode(array(
                "r" => False,
                "errors" => array("Vous n'avez pas accès à ce module")
            ));
            exit;
        }
    }

    public static function isAdministrator() : bool {
        return (intval($_SESSION['role']) < $this->$ROLES['ADMINISTRATOR']);
    }
}
