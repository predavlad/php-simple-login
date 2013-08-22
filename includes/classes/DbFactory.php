<?php

/**
 * Class DbFactory
 */
class DbFactory
{
    /**
     * @var PDO
     */
    private static $_db = null;

    /**
     * @return PDO
     */
    public static function getInstance()
    {
        if (is_null(static::$_db)) {
            static::$_db = new PDO('mysql:dbname=' . DB_NAME . ';host=localhost', DB_USER, DB_PASS);
            static::$_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        }
        return static::$_db;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

}