<?php

namespace Winged\Database;

class Connections
{
    public static $connections = [];
    public static $default = 'winged';

    /**
     * init fisrt default Database connection using params founded in ./config.php
     * @param string $nickname
     */
    public static function init($nickname = 'winged')
    {
        self::$connections[$nickname] = (new Database())->connect();
        self::$connections[$nickname]->nickname = $nickname;
        CurrentDB::$current = self::$connections[$nickname];
    }

    /**
     * @param string $key
     * @return Database
     */
    public static function getDb($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            return self::$connections[$key];
        }
    }

    /**
     * engine use current connection for insert, select and update queries
     * @param $key string
     * @return bool
     */
    public static function setCurrent($key = '')
    {
        if (array_key_exists($key, self::$connections)) {
            if (get_class(self::$connections[$key]) == 'Database') {
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
     * @param $db Database
     * @param $key string
     */
    public static function newDb($db, $key = '', $set_current = false)
    {
        self::$connections[$key] = $db;
        if ($set_current) {
            self::setCurrent($key);
        }
    }

}