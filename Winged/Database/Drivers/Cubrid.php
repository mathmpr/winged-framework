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
class Cubrid extends Eloquent implements EloquentInterface
{

    public function setEncoding()
    {
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->DATABASE_CHARSET);
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