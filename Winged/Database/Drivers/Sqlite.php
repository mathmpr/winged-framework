<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 *
 * @package Winged\Database\Drivers
 */
class Sqlite
{

    public function setEncoding(){}

    /**
     * Return query string for show tables in current database
     *
     * @return string
     */
    public function show()
    {
        return "SELECT name FROM sqlite_master WHERE type = 'table'";
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
        return "PRAGMA TABLE_INFO({$tableName})";
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
        $clear_fields = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $clear_fields[$field['name']] = [
                    'name' => $field['name'],
                    'not_null' => $field['notnull'] == 1 ? true : false,
                    'default' => $field['dflt_value'],
                    'pk' => $field['pk'] == 1 ? true : false,
                    'type' => trim(preg_replace("/\([^)]+\)/", '', $field['type'])),
                ];
            }
        }
        return $clear_fields;
    }

    public function remove()
    {

    }

}