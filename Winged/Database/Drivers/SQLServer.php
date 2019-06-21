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
        ini_set('mssql.charset', WingedConfig::$config->db()->DATABASE_CHARSET);
        SQLSRV_PHPTYPE_STRING(WingedConfig::$config->db()->DATABASE_CHARSET);
        CurrentDB::execute('SET CLIENT_ENCODING TO \'UTF8\'');
    }

    /**
     * Return query string for show tables in current database
     *
     * @return string
     */
    public function show()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function showMiddleware($fields = [])
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return [];
    }

    /**
     * Return query to fetch table information in current database
     *
     * @param string $tableName
     *
     * @return string
     */
    public function describe($tableName = '')
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function describeMiddleware($fields = [])
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return [];
    }

    /**
     * get initial query for the selected command
     *
     * @return $this|EloquentInterface
     */
    public function parseQuery()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds join clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseJoin()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds where clause on $this->currentQueryString
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function parseWhere()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseGroup()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function parseHaving()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds order by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseOrder()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds set clause for update queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseSet()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseValues()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds limit clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseLimit()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds table names for select queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseFrom()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds names of field for select queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseSelect()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds delete and from clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseDelete()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds updated tables in update clase on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseUpdate()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * adds values for insert queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseInto()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

    /**
     * prepare any query for after build query and execute then
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function prepare()
    {
        /*
         * @TODO content of this function for SQLServer driver
         */
        return $this;
    }

}