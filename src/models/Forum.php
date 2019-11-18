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
            ON tf_forum_category.id = tf_forum_post.category
            WHERE tf_forum_category.id=:cid
            GROUP BY tf_forum_category.id, tf_forum_category.title,
               tf_forum_category.created_at, tf_forum_category.updated_at
            LIMIT 1"
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
            "SELECT tf_forum_post.*, COUNT(tf_post_2.post_response) AS count_responses
             FROM tf_forum_post
             LEFT JOIN tf_forum_post AS tf_post_2
             ON tf_forum_post.id = tf_post_2.post_response
             WHERE tf_forum_post.category=:cid AND tf_forum_post.post_response IS NULL
             GROUP BY tf_forum_post.id, tf_forum_post.author, tf_forum_post.title,
                tf_forum_post.content,
                tf_forum_post.created_at, tf_forum_post.updated_at,
                tf_forum_post.category, tf_forum_post.post_response
             ORDER BY tf_forum_post.updated_at"
        );
        $req->execute(array('cid' => $category_id));
        return ($req->fetchAll());
    }
}
