<?php

class Database {

    private $PDOInstance = null;

    private static $instance = null;

    const DEFAULT_SQL_USER = 'root';
    const DEFAULT_SQL_HOST = 'localhost';
    const DEFAULT_SQL_PASS = '';
    const DEFAULT_SQL_DTB = 'testify';

    private function __construct() {
        $this->PDOInstance = new PDO(
            'mysql:dbname='.self::DEFAULT_SQL_DTB.';host='.self::DEFAULT_SQL_HOST,
            self::DEFAULT_SQL_USER,
            self::DEFAULT_SQL_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
        );
        $this->PDOInstance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->PDOInstance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function getInstance() : Database {
        if(is_null(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getPDO() {
        return $this->PDOInstance;
    }
}
