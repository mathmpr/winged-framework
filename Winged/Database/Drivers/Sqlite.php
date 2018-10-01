<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class Sqlite
{

    public function setNames(){}

    /**
     * Return query string for show tables in current database
     * @return string
     */
    public function showTables()
    {
        return "SELECT name FROM sqlite_master WHERE type = 'table'";
    }

}