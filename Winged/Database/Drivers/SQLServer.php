<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use Winged\WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class SQLServer{

    public function setEncoding(){
        ini_set('mssql.charset', WingedConfig::$config->DATABASE_CHARSET);
        SQLSRV_PHPTYPE_STRING(WingedConfig::$config->DATABASE_CHARSET);
        CurrentDB::execute('SET CLIENT_ENCODING TO \'UTF8\'');
    }

}