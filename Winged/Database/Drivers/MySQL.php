<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use Winged\WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class MySQL
{

    public function setNames()
    {
        CurrentDB::execute('SET NAMES ' . WingedConfig::$DATABASE_CHARSET);
    }

    /**
     * Return query string for show tables in current database
     * @return string
     */
    public function showTables()
    {
        return "SHOW TABLES";
    }

    
    public function descTable($tableName = '')
    {
        return "DESC " . $tableName;
    }

}