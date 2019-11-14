<?php

namespace Testify\Model;

use Testify\Model\Database;

class Forum {

    private static $instance = null;

    private function __construct() {}

    public static function getInstance() : Forum {
        if(is_null(self::$instance)) {
            self::$instance = new Forum();
        }
        return self::$instance;
    }

    public function getCategories() {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT * FROM tf_forum_category ORDER BY `created_at`"
        );
        $req->execute();
        return ($req->fetchAll());
    }
}
