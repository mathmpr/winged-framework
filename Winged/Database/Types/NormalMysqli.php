<?php

namespace Winged\Database\Types;

use Winged\Database\Database;

class NormalMysqli
{
    /** @var $refer mysqli */
    private $refer = null;

    /** @var $last_stmt mysqli_stmt */
    public $last_stmt = null;

    /** @var $last_result mysqli_result */
    public $last_result = null;

    public function __construct(\mysqli $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '')
    {
        $stmt = $this->refer->query($query);
        $this->last_stmt = $stmt;
        return $stmt === true ? true : false;
    }

    public function insert($query = '')
    {
        $stmt = $this->refer->query($query);
        $this->last_stmt = $stmt;
        return $stmt === true ? $this->refer->insert_id : false;
    }

    public function fetch($query = '', $register_last = true)
    {
        $stmt = $this->refer->query($query);
        if ($register_last !== false) {
            $this->last_stmt = $stmt;
        }
        if ($stmt) {
            $tuple = [];
            while ($row = $stmt->fetch_assoc()) {
                $tuple[] = $row;
            }
            return empty($tuple) ? null : $tuple;
        }
        return null;
    }

    public function count($query = '')
    {
        if ($query === '') {
            return $this->refer->affected_rows !== -1 ? $this->refer->affected_rows : 0;
        }
        $stmt = $this->refer->query($query);
        if ($stmt !== false && $stmt !== null) {
            return $this->refer->affected_rows;
        }
        return null;
    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], $register_last);
                    $desc = [];
                    foreach ($result as $field) {
                        $name = $field['Field'];
                        unset($field['Field']);
                        $desc[$name] = [];
                        foreach ($field as $key => $prop) {
                            $desc[$name][strtolower($key)] = $prop;
                        }
                    }
                    unset($result);
                    return $desc;
                }
                break;
            case Database::SP_SHOW_TABLES:
                $result = $this->fetch('SHOW TABLES', $register_last);
                $tables = [];
                foreach ($result as $table) {
                    $keys = array_keys($table);
                    $tables[] = $table[$keys[0]];
                }
                return $tables;
                break;
            default:
                return null;
                break;
        }
        return null;
    }

}