<?php

namespace Winged\Database;

use Winged\WingedConfig;

class CurrentDB
{
    /**
     * @var $current Database
     */
    public static $current;

    public static function execute($query, $args = [])
    {
        return self::$current->execute($query, $args);
    }

    public static function insert($query, $args = [])
    {
        return self::$current->insert($query, $args);
    }

    public static function fetch($query, $args = [])
    {
        return self::$current->fetch($query, $args);
    }

    public static function count($query = '', $args = [])
    {
        return self::$current->count($query, $args);
    }

    public static function sp($param, $args = [])
    {
        return self::$current->sp($param, $args);
    }

    public static function tableExists($table_name)
    {
        if (in_array($table_name, self::$current->db_tables)) {
            return true;
        }
        return false;
    }

    public static function lastError()
    {
        if (self::$current->isMysqli()) {
            if (self::$current->abstract->last_stmt->error) {
                return self::$current->abstract->last_stmt->error;
            }
            return false;
        } else {
            if (self::$current->abstract->last_stmt->errorInfo()) {
                return self::$current->abstract->last_stmt->errorInfo();
            }
            return false;
        }
    }

}