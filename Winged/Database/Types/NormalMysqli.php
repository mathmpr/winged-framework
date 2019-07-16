<?php

namespace Winged\Database\Types;

use Winged\Database\CurrentDB;

/**
 * Class NormalMysqli
 *
 * @package Winged\Database\Types
 */
class NormalMysqli
{
    /** @var $refer \mysqli */
    private $refer = null;

    /** @var $last_stmt \PDOStatement */
    public $last_stmt = null;

    /** @var $affected_rows int */
    public $affected_rows = 0;

    /** @var $sqlstate string */
    public $sqlstate = '';

    /** @var $error_code int */
    public $error_code = 0;

    /** @var $error_info string */
    public $error_info = '';

    /**
     * NormalMysqli constructor.
     *
     * @param \mysqli $db
     */
    public function __construct(\mysqli $db)
    {
        $this->refer = $db;
    }

    /**
     * prepare and execute any query, return resolved stmt after
     *
     * @param string $query
     *
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    private function querying($query = '')
    {
        $stmt = $this->refer->query($query);
        if ($stmt === false) {
            trigger_error("DB error: can't query - " . $query . ' : ' . $this->refer->error, E_USER_ERROR);
            $this->affected_rows = false;
            $this->sqlstate = false;
            $this->error_code = false;
            $this->error_info = false;
        } else {
            $this->last_stmt = $stmt;
            $this->affected_rows = $this->refer->affected_rows;
            $this->sqlstate = $this->refer->sqlstate;
            $this->error_code = $this->refer->errno;
            $this->error_info = $this->refer->error;
            return $stmt;
        }
        return false;
    }

    /**
     * execute update and delete queries into database
     *
     * @param string $query
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($query = '')
    {
        return $this->querying($query) ? true : false;
    }

    /**
     * execute insert queries into database
     *
     * @param string $query
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function insert($query = '')
    {
        return $this->querying($query) ? $this->refer->insert_id : false;
    }

    /**
     * execute select queries into database
     *
     * @param string $query
     *
     * @return array|bool|null
     * @throws \Exception
     */
    public function fetch($query = '')
    {
        $result = $this->querying($query);
        if ($result) {
            $tuple = [];
            while ($row = $result->fetch_assoc()) {
                $tuple[] = $row;
            }
            return empty($tuple) ? null : $tuple;
        }
        return false;
    }

    /**
     * execute count queries into database
     *
     * @param string $query
     *
     * @return bool|int
     * @throws \Exception
     */
    public function count($query = '')
    {
        return $this->querying($query) ? $this->refer->affected_rows : false;
    }

    /**
     * describe table into database
     *
     * @param $tableName
     *
     * @return array
     * @throws \Exception
     */
    public function describe($tableName)
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->describe($tableName));
        return CurrentDB::$current->queryStringHandler->describeMiddleware($result);
    }

    /**
     * show tables in database
     *
     * @return array
     * @throws \Exception
     */
    public function show()
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->show());
        return CurrentDB::$current->queryStringHandler->showMiddleware($result);
    }

    /**
     * close this conection
     */
    public function close()
    {
        $this->refer->close();
        $this->refer = null;
    }

}