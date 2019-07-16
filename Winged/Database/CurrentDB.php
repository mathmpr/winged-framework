<?php

namespace Winged\Database;


/**
 * Class CurrentDB
 *
 * @package Winged\Database
 */
class CurrentDB
{
    /**
     * @var $current Database
     */
    public static $current;

    /**
     * @param       $query
     * @param array $args
     *
     * @return array|bool|string
     * @throws \Exception
     */
    public static function execute($query, $args = [])
    {
        return self::$current->execute($query, $args);
    }

    /**
     * @param       $query
     * @param array $args
     *
     * @return array|bool|false|int|mixed|string
     * @throws \Exception
     */
    public static function insert($query, $args = [])
    {
        return self::$current->insert($query, $args);
    }

    /**
     * @param       $query
     * @param array $args
     *
     * @return array|false|string|null
     * @throws \Exception
     */
    public static function fetch($query, $args = [])
    {
        return self::$current->fetch($query, $args);
    }

    /**
     * @param string $query
     * @param array  $args
     *
     * @return int|mixed|string|null
     * @throws \Exception
     */
    public static function count($query = '', $args = [])
    {
        return self::$current->count($query, $args);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function show()
    {
        return self::$current->show();
    }

    /**
     * @param $tableName
     *
     * @return array
     * @throws \Exception
     */
    public static function describe($tableName)
    {
        return self::$current->describe($tableName);
    }

    /**
     * check if table name exists in db_tables
     *
     * @param $tableName
     *
     * @return bool
     */
    public static function exists($tableName)
    {
        if (array_key_exists($tableName, self::$current->db_tables)) {
            return true;
        }
        return false;
    }

    /**
     * return last error in current connection
     *
     * @return array|bool
     */
    public static function lastError()
    {
        if (!is_bool(self::$current->abstract->error_code) && self::$current->abstract->error_code !== 0) {
            return self::$current->abstract->error_code;
        }
        return false;
    }

    /**
     * close connection
     */
    public static function close()
    {
        self::$current->abstract->close();
    }

}