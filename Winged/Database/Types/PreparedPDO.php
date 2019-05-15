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

    public function describe($tableName)
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->describe($tableName), []);
        return CurrentDB::$current->queryStringHandler->describeMiddleware($result);
    }

    public function show()
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->show(), []);
        return CurrentDB::$current->queryStringHandler->showMiddleware($result);
    }

    private function bind_prepare(&$query = '', &$args = [])
    {
        if (count7($args) > 0) {
            $query = str_replace('?', '%s', $query);
            $argsNames = [];
            $forSprintf = [$query];
            foreach ($args as $key => $value) {
                $argsNames[':' . $key] = $value;
                $forSprintf[] = ':' . $key;
            }
            $query = call_user_func_array('sprintf', $forSprintf);
            try {
                $stmt = $this->refer->prepare($query);
            } catch (\PDOException $error) {
                $stmt = false;
            }
            if ($stmt === false) {
                return $stmt;
            }
            foreach ($argsNames as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt;
        } else {
            return $this->refer->prepare($query);
        }
    }

}