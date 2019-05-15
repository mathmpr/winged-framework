<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 *
 * @package Winged\Database\Drivers
 */
class SQLServer extends Eloquent implements EloquentInterface
{

    public function setEncoding()
    {
        ini_set('mssql.charset', WingedConfig::$config->DATABASE_CHARSET);
        SQLSRV_PHPTYPE_STRING(WingedConfig::$config->DATABASE_CHARSET);
        CurrentDB::execute('SET CLIENT_ENCODING TO \'UTF8\'');
    }

    public function parseQuery()
    {
    }

    public function parseJoin()
    {
    }

    public function parseWhere()
    {
    }

    public function parseGroup()
    {
    }

    public function parseHaving()
    {
    }

    public function parseOrder()
    {
    }

    public function parseSet()
    {
    }

    public function parseValues()
    {
    }

}