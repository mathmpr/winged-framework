<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class Cubrid{

    public function setEncoding(){
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->DATABASE_CHARSET);
    }



}