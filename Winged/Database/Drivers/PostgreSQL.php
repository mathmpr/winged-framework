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
class PostgreSQL extends DriverMiddleware
{

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

}