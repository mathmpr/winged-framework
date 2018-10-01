<?php

namespace Winged\Database\Types;

use Winged\Database\CurrentDB;
use Winged\Error\Error;
use Winged\Database\Database;

class PreparedPDO
{

    /** @var $refer \PDO */
    private $refer = null;

    /** @var $last_stmt \PDOStatement */
    public $last_stmt = null;

    /** @var $last_result \PDOStatement */
    public $last_result = null;

    public function __construct(\PDO $db)
    {
        $this->refer = $db;
    }

    public function execute($query = '', $args = [])
    {
        $stmt = $this->bind_prepare($query, $args);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\PDOException $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? true : false;
        }
    }

    public function insert($query = '', $args = [])
    {
        $stmt = $this->bind_prepare($query, $args);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo(), __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\PDOException $error) {
                Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
            }
            $this->last_stmt = $ret !== false ? $stmt : false;
            return $ret !== false ? $this->refer->lastInsertId() : false;
        }
    }

    public function fetch($query = '', $args = [], $register_last = true)
    {
        $stmt = $this->bind_prepare($query, $args);
        if ($stmt === false) {
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo()[2], __FILE__, __LINE__);
            return $this->refer->errorInfo();
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
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
        $query = explode('FROM', $query);
        if (count7($query) >= 2) {
            array_shift($query);
            $query = 'SELECT COUNT(*) FROM' . implode('', $query);
        }
        if ($query === '') {
            return $this->last_stmt !== null && $this->last_stmt !== false ? $this->last_stmt->rowCount() : 0;
        } else {
            $stmt = $this->bind_prepare($query, $args);
            if ($stmt !== false && $stmt !== null) {
                try {
                    $stmt->execute();
                } catch (\PDOException $error) {
                    Error::push(__CLASS__, "DB error: " . $error->getMessage(), __FILE__, __LINE__);
                }
                return $stmt->fetchColumn();
            }
            Error::push(__CLASS__, "DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo()[0], __FILE__, __LINE__);
            return null;
        }
    }

    public function sp($param = '', $args = [], $register_last = false)
    {
        switch ($param) {
            case Database::SP_DESC_TABLE:
                if (array_key_exists('table_name', $args)) {
                    $result = $this->fetch(CurrentDB::$current->queryStringHandler->descTable($args['table_name']), [], $register_last);
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
                $result = $this->fetch(CurrentDB::$current->queryStringHandler->showTables(), [], $register_last);
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

    private function bind_prepare(&$query = '', &$args = [])
    {
        if (count7($args) > 0) {
            $query = str_replace('?', '%s', $query);
            $fields = [$query];
            foreach ($args as $key => $value) {
                $_key = ':' . str_replace(['i-', 's-', 'd-', 'b-', '-'], '', $key);
                $args[$_key] = $value;
                unset($args[$key]);
                $fields[] = $_key;
            }
            $query = call_user_func_array('sprintf', array_merge($fields));
            try {
                $stmt = $this->refer->prepare($query);
            } catch (\PDOException $error) {
                $stmt = false;
            }
            if ($stmt === false) {
                return $stmt;
            }
            foreach ($args as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt;
        } else {
            return $this->refer->prepare($query);
        }
    }

}