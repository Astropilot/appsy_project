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
            "SELECT tf_forum_category.*, COUNT(tf_forum_post.category) AS count_posts
             FROM tf_forum_category
             LEFT JOIN tf_forum_post
             ON tf_forum_category.id = tf_forum_post.category
             GROUP BY tf_forum_category.id, tf_forum_category.title,
                tf_forum_category.created_at, tf_forum_category.updated_at
             ORDER BY tf_forum_category.created_at"
        );
        $req->execute();
        return ($req->fetchAll());
    }

    public function getCategory($category_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT tf_forum_category.*, COUNT(tf_forum_post.category) AS count_posts
            FROM tf_forum_category
            LEFT JOIN tf_forum_post
            ON tf_forum_category.id = tf_forum_post.category AND tf_forum_category.id=:cid
            GROUP BY tf_forum_category.id, tf_forum_category.title,
               tf_forum_category.created_at, tf_forum_category.updated_at"
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

    public function getPosts($category_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT *
             FROM tf_forum_post
             WHERE `category`=:cid
             ORDER BY `updated_at`"
        );
        $req->execute(array('cid' => $category_id));
        return ($req->fetchAll());
    }
}
