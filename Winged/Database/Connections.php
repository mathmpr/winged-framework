<?php

namespace Winged\Database;

/**
 * Class Connections
 *
 * @package Winged\Database
 */
class Connections
{
    /**
     * @var Database[]
     */
    public static $connections = [];
    public static $default = 'winged';

    /**
     * init fisrt default Database connection using params founded in ./config.php
     *
     * @param string $nickname
     */
    public static function init($nickname = 'winged')
    {
        try {
            (new Database(false, false, $nickname))->connect();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * @param string $key
     *
     * @return Database | bool
     */
    public static function getDb($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            return self::$connections[$key];
        }
        return false;
    }

    /**
     * engine use current connection for insert, select and update queries
     *
     * @param $key string
     *
     * @return bool
     */
    public static function setCurrent($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            if (get_class(self::$connections[$key]) == 'Winged\Database\Database') {
                if (isset(self::$connections[$key])) {
                    self::$default = $key;
                    CurrentDB::$current = self::$connections[$key];
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * optional register Database object in connections
     *
     * @param $db  Database
     * @param $key string
     */
    public static function newDb($db, $key = '', $set_current = false)
    {
        self::$connections[$key] = $db;
        if ($set_current) {
            self::setCurrent($key);
        }
    }

    /**
     * close all connections
     */
    public static function closeAll()
    {
        if(!empty(self::$connections)){
            foreach (self::$connections as $key => $connection) {
                $connection->abstract->close();
                unset(self::$connections[$key]);
            }
        }
    }

}