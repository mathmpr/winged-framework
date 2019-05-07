<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 * @package Winged\Database\Drivers
 */
class MySQL extends DriverMiddleware
{

    public function setEncoding()
    {
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->db()->DATABASE_CHARSET);
    }

    /**
     * Return query string for show tables in current database
     * @return string
     */
    public function show()
    {
        return "SHOW TABLES";
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
                $clear_fields[$field['Tables_in_' . $this->database->dbname]] = [
                    'table_name' => $field['Tables_in_' . $this->database->dbname],
                ];
            }
        }
        return $clear_fields;
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
        return "DESC " . $tableName;
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
                $clear_fields[$field['Field']] = [
                    'field' => $field['Field'],
                    'not_null' => $field['Null'] == 1 ? true : false,
                    'default' => $field['Default'],
                    'pk' => $field['Key'] === "PRI" ? true : false,
                    'type' => trim(preg_replace("/\([^)]+\)/", '', $field['Type'])),
                    'extra' => $field['Extra']
                ];
            }
        }
        return $clear_fields;
    }



}