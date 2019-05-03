<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class Firebird{

    public function setEncoding(){
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->DATABASE_CHARSET);
    }

}