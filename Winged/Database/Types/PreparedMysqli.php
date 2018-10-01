<?php

namespace Winged\Database\Types;

use Winged\Error\Error;
use Winged\Database\Database;
use Winged\WingedConfig;

class PreparedMysqli
{

    /** @var $refer \mysqli */
    private $refer = null;

    /** @var $last_stmt \mysqli_stmt */
    public $last_stmt = null;

    /** @var $last_result \mysqli_result */
    public $last_result = null;

    public function __construct(\mysqli $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            return $this->refer->error;
        }
        if (empty($args)) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\mysqli_sql_exception $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            return $ret !== false && $ret !== null ? true : false;
        } else {
            if ($this->bind_param($stmt, $args)) {
                $ret = false;
                try {
                    $ret = $stmt->execute();
                } catch (\mysqli_sql_exception $error) {
                    Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
                }
                $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
                return $ret !== false && $ret !== null ? true : false;
            }
        }
        return false;
    }

    public function insert($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, __FILE__, __LINE__);
            return $this->refer->error;
        }

        if ($this->bind_param($stmt, $args)) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\mysqli_sql_exception $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            return $ret !== false ? $this->refer->insert_id : false;
        }
        return false;
    }

    public function fetch($query = '', $args = [], $register_last = true)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, __FILE__, __LINE__);
            return $this->refer->error;
        }

        $cont = true;

        if ($stmt->param_count > 0) {
            $cont = $this->bind_param($stmt, $args);
        }

        if ($cont) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\mysqli_sql_exception $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            if ($register_last) {
                $this->last_stmt = $ret !== false && $ret !== null ? $stmt : false;
            }
            if ($ret !== false) {
                $ret = $stmt->get_result();
                if ($ret !== false && $ret !== null) {
                    if ($register_last !== false) {
                        $this->last_result = $ret;
                    }
                    $tuple = [];
                    while ($row = $ret->fetch_assoc()) {
                        $tuple[] = $row;
                    }
                    return empty($tuple) ? null : $tuple;
                }
            }
        }
        return null;
    }

    public function count($query = '', $args = [])
    {
        $ret = false;

        if ($query === '') {
            return $this->last_result ? ($this->last_result->num_rows > 0 ? $this->last_result->num_rows : 0) : $this->last_stmt ? ($this->last_stmt->affected_rows > -1 ? $this->last_stmt->affected_rows : 0) : 0;
        }

        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query, __FILE__, __LINE__);
            return $this->refer->error;
        }

        $cont = true;

        if ($stmt->param_count > 0) {
            $cont = $this->bind_param($stmt, $args);
        }

        if ($cont) {
            try {
                $stmt->execute();
                $this->last_stmt = $stmt;
                $ret = $stmt->get_result();
                if ($ret) {
                    $this->last_result = $ret;
                }
            } catch (\mysqli_sql_exception $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            return $ret ? ($ret->num_rows > 0 ? $ret->num_rows : 0) : $stmt ? ($stmt->affected_rows > -1 ? $stmt->affected_rows : 0) : 0;
        }
        return 0;

    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch('DESC ' . $args['table_name'], [], $register_last);
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
                $result = $this->fetch('SHOW TABLES', [], $register_last);
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

    /**
     * @param $stmt \mysqli_stmt
     * @param $args array
     * @return bool
     */
    private function bind_param(&$stmt, $args)
    {
        $ref = [];
        foreach ($args as $key => $arg) {
            $ref[] =& $args[$key];
        }
        return call_user_func_array([$stmt, 'bind_param'], $ref);
    }
}