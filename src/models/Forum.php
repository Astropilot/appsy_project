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

    public function getCategory($category_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT id, title, created_at, updated_at FROM tf_forum_category WHERE `id`=:cid"
        );
        $req->execute(array(
            'cid'=> $category_id
        ));
        return ($req->fetch());
    }

    public function createCategory($name) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_forum_category SET `title`=:title, `updated_at`=NOW()"
        );
        $req->execute(array('title' => $name));

        $category_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getCategory($category_id);
    }
}
