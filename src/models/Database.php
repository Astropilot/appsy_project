<?php

namespace Testify\Model;

class Database {

    private $PDOInstance = null;

    private static $instance = null;

    private function __construct($config) {
        $DEFAULT_SQL_USER = $config::SQL_USER;
        $DEFAULT_SQL_HOST = $config::SQL_HOST;
        $DEFAULT_SQL_PASS = $config::SQL_PASS;
        $DEFAULT_SQL_DTB = $config::SQL_DTB;

        $this->PDOInstance = new \PDO(
            'mysql:dbname='.$DEFAULT_SQL_DTB.';host='.$DEFAULT_SQL_HOST,
            $DEFAULT_SQL_USER,
            $DEFAULT_SQL_PASS,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
        );
        $this->PDOInstance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->PDOInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->PDOInstance->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function getInstance($config=NULL) : Database {
        if(is_null(self::$instance)) {
            self::$instance = new Database($config);
        }
        return self::$instance;
    }

    public function getPDO() {
        return $this->PDOInstance;
    }
}
