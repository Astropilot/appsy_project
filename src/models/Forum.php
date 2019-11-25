<?php

namespace Testify\Model;

use Testify\Model\Database;
use Testify\Model\User;

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
             WHERE tf_forum_post.post_response IS NULL
             GROUP BY tf_forum_category.id, tf_forum_category.title,
                tf_forum_category.created_at, tf_forum_category.updated_at
             ORDER BY tf_forum_category.display_order ASC"
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
            WHERE tf_forum_category.id=:cid AND tf_forum_post.post_response IS NULL
            GROUP BY tf_forum_category.id, tf_forum_category.title,
               tf_forum_category.created_at, tf_forum_category.updated_at
            LIMIT 1"
        );
        $req->execute(array(
            'cid'=> $category_id
        ));
        return ($req->fetch());
    }

    public function getNewCategoryDisplayOrder() {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT COALESCE(MAX(display_order) + 1, 0) FROM tf_forum_category"
        );
        $req->execute();
        return ($req->fetchColumn());
    }

    public function getSiblingCategoryOrder($order, $direction) {
        if ($direction === "up")
            $sql = "SELECT COALESCE(MAX(display_order), -1) FROM tf_forum_category WHERE `display_order`<:order";
        else
            $sql = "SELECT COALESCE(MIN(display_order), -1) FROM tf_forum_category WHERE `display_order`>:order";
        $req = Database::getInstance()->getPDO()->prepare(
            $sql
        );
        $req->execute(array(
            'order' => $order
        ));
        return ($req->fetchColumn());
    }

    public function updateCategoryDisplayOrder($category_id, $display_order) {
        $req = Database::getInstance()->getPDO()->prepare(
            "UPDATE tf_forum_category SET `display_order`=:order WHERE `id`=:id"
        );
        return $req->execute(array(
            'id' => $category_id,
            'order' => $display_order
        ));
    }

    public function getCategoryFromDisplayOrder($display_order) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT *
             FROM tf_forum_category
             WHERE `display_order`=:order
             LIMIT 1"
        );
        $req->execute(array(
            'order'=> $display_order
        ));
        return ($req->fetch());
    }

    public function createCategory($name, $description, $display_order) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_forum_category SET `title`=:title, `description`=:description, `display_order`=:order, `updated_at`=NOW()"
        );
        $req->execute(array(
            'title' => $name,
            'description' => $description,
            'order' => $display_order
        ));

        $category_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getCategory($category_id);
    }

    public function deleteCategory($category_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "DELETE FROM tf_forum_category
             WHERE `id`=:cid"
        );
        return $req->execute(array('cid' => $category_id));
    }

    public function getPosts($category_id) {
        $posts = array();

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

        while ($row = $req->fetch()) {
            $row['content'] = html_entity_decode($row['content']);
            array_push($posts, $row);
        }

        return ($posts);
    }

    public function getPost($post_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT tf_forum_post.*, COUNT(tf_post_2.post_response) AS count_responses
             FROM tf_forum_post
             LEFT JOIN tf_forum_post AS tf_post_2
             ON tf_forum_post.id = tf_post_2.post_response
             WHERE tf_forum_post.id=:pid
             GROUP BY tf_forum_post.id, tf_forum_post.author, tf_forum_post.title,
                tf_forum_post.content,
                tf_forum_post.created_at, tf_forum_post.updated_at,
                tf_forum_post.category, tf_forum_post.post_response
             ORDER BY tf_forum_post.updated_at
             LIMIT 1"
        );
        $req->execute(array('pid' => $post_id));

        $post = $req->fetch();
        $post['author'] = User::getInstance()->getUser($post['author']);
        $post['content'] = html_entity_decode($post['content']);
        return ($post);
    }

    public function createPost($author, $category_id, $title, $content, $response=null) {
        $req = Database::getInstance()->getPDO()->prepare(
            "INSERT INTO tf_forum_post SET `author`=:uid, `title`=:title,
            `content`=:content, `updated_at`=NOW(), `category`=:cid, `post_response`=:reponse"
        );
        $req->execute(array(
            'uid' => $author,
            'title' => $title,
            'content' => $content,
            'cid' => $category_id,
            'reponse' => $response
        ));

        $post_id = Database::getInstance()->getPDO()->lastInsertId();
        return self::getPost($post_id);
    }

    public function getPostResponses($post_id) {
        $responses = array();

        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT *
             FROM tf_forum_post
             WHERE (`post_response` IS NOT NULL AND `post_response`=:pid)
                OR
                   (`id`=:pid2 AND `post_response` IS NULL)
             ORDER BY `created_at`"
        );
        $req->execute(array('pid' => $post_id, 'pid2' => $post_id));

        while ($row = $req->fetch()) {
            $row['author'] = User::getInstance()->getUser($row['author']);
            $row['content'] = html_entity_decode($row['content']);
            array_push($responses, $row);
        }
        return $responses;
    }

    public function getPostResponse($post_id, $response_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "SELECT *
             FROM tf_forum_post
             WHERE `id`=:rid AND `post_response`=:pid
             LIMIT 1"
        );
        $req->execute(array('pid' => $post_id, 'rid' => $response_id));
        $row = $req->fetch();
        $row['author'] = User::getInstance()->getUser($row['author']);
        return $row;
    }

    public function deletePostResponse($post_id, $response_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "DELETE FROM tf_forum_post
             WHERE `id`=:rid AND `post_response`=:pid"
        );
        return $req->execute(array('pid' => $post_id, 'rid' => $response_id));
    }

    public function deletePost($post_id) {
        $req = Database::getInstance()->getPDO()->prepare(
            "DELETE FROM tf_forum_post
             WHERE `id`=:pid AND `post_response` IS NULL"
        );
        return $req->execute(array('pid' => $post_id));
    }
}
