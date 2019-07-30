<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class Firebird{

    public function setNames(){
        CurrentDB::execute('SET CLIENT_ENCODING TO \'UTF8\'');
    }

}