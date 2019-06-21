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
class PostgreSQL extends Eloquent implements EloquentInterface
{

    /**
     * set default encoding to client conection
     */
    public function setEncoding()
    {
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->db()->DATABASE_CHARSET);
    }

    /**
     * Return query string for show tables in current database
     *
     * @return string
     */
    public function show()
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '" . $this->database->schema . "' ORDER BY table_schema,table_name";
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
        $clear_fields = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $clear_fields[$field['table_name']] = [
                    'table_name' => $field['table_name'],
                ];
            }
        }
        return $clear_fields;
    }

    /**
     * Return query string for describe a table schema in current database
     *
     * @param string $tableName
     *
     * @return string
     */
    public function describe($tableName = '')
    {
        return "PRAGMA table_info([" . $tableName . "])";
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
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
         * @TODO content of this function for PostgreSQL driver
         */
        return $this;
    }

}