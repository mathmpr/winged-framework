<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use Winged\WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class PostgreSQL
{

    public function setNames()
    {
        CurrentDB::execute('SET CLIENT_ENCODING TO \'UTF8\'');
    }

    /**
     * Return query string for show tables in current database
     * @return string
     */
    public function showTables()
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '" . WingedConfig::$config->SCHEMA . "' ORDER BY table_schema,table_name";
    }

    /**
     * Return query string for describe a table schema in current database
     * @return string
     */
    public function descTable($tableName = '')
    {
        return "PRAGMA table_info([" . $tableName . "])";
    }

}