<?php

namespace Winged\Database\Types;

use Winged\Error\Error;
use Winged\Database\Database;

class PreparedPDO
{

    /** @var $refer PDO */
    private $refer = null;

    /** @var $last_stmt PDOStatement */
    public $last_stmt = null;

    /** @var $last_result PDOStatement */
    public $last_result = null;

    public function __construct(\PDO $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute($args);
            } catch (\PDOException $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? true : false;
        }
    }

    public function insert($query = '', $args = [])
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute($args);
            } catch (\PDOException $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? $this->refer->lastInsertId() : false;
        }
    }

    public function fetch($query = '', $args = [], $register_last = true)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;

            try {
                $ret = $stmt->execute($args);
            } catch (\PDOException $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            if ($register_last !== false) {
                $this->last_stmt = $ret !== false ? $stmt : false;
            }

            if ($ret) {
                $all = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                return empty($all) || $all === false ? null : $all;
            } else {
                return null;
            }
        }
    }

    public function count($query = '', $args = [])
    {
        if ($query === '') {
            return $this->last_stmt !== null && $this->last_stmt !== false ? $this->last_stmt->rowCount() : 0;
        } else {
            $count = 0;
            $stmt = $this->refer->prepare($query);
            if ($stmt !== false && $stmt !== null) {
                $stmt->execute($args);
                if ($stmt->fetchColumn() > 0) {
                    foreach ($this->refer->query($query) as $row) {
                        $count++;
                    }
                }
                return $count;
            }
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return null;
        }
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
}