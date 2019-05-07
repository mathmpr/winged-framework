<?php

namespace Winged\Database\Types;

use Winged\Database\Database;

class NormalMysqli
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

}