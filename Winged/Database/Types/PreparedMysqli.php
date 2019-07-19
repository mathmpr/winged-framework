<?php

namespace Winged\Database\Types;

use Winged\Database\CurrentDB;

/**
 * Class PreparedMysqli
 *
 * @package Winged\Database\Types
 */
class PreparedMysqli
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
     * PreparedMysqli constructor.
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
     * @param array  $args
     *
     * @return bool|\mysqli_stmt
     * @throws \Exception
     */
    private function querying($query = '', $args = [])
    {
        if (count7($args) > 0) {
            $stmt = $this->refer->prepare($query);
            if ($stmt === false) {
                trigger_error("DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, E_USER_ERROR);
            } else {
                $ref = [];
                foreach ($args as $key => $arg) {
                    $ref[] =& $args[$key];
                }
                $stmt->bind_param(...$ref);
            }
        } else {
            $stmt = $this->refer->prepare($query);
            if ($stmt === false) {
                trigger_error("DB error: can't prepare query - " . $query . ' : ' . $this->refer->error, E_USER_ERROR);
            }
        }
        if ($stmt) {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\mysqli_sql_exception $error) {
                trigger_error("DB error: " . $error->getMessage());
            }
            if ($ret) {
                $this->last_stmt = $stmt;
                $this->affected_rows = $stmt->num_rows;
                $this->sqlstate = $this->refer->sqlstate;
                $this->error_code = $this->refer->errno;
                $this->error_info = $this->refer->error;
                return $stmt;
            } else {
                $this->affected_rows = false;
                $this->sqlstate = false;
                $this->error_code = false;
                $this->error_info = false;
            }
        }
        return false;
    }

    /**
     * method for the purpose of executing queries to delete or update data in the database
     *
     * @param string $query
     * @param array  $args
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($query = '', $args = [])
    {
        $stmt = $this->querying($query, $args);
        if ($stmt) {
            return true;
        }
        return false;
    }

    /**
     * method for the purpose of executing queries to insert data into database
     *
     * @param string $query
     * @param array  $args
     *
     * @return int|false
     * @throws \Exception
     */
    public function insert($query = '', $args = [])
    {
        $stmt = $this->querying($query, $args);
        if ($stmt) {
            return $this->refer->insert_id;
        }
        return false;
    }


    /**
     * method for the purpose of executing queries to retrieve data from the database
     *
     * @param string $query
     * @param array  $args
     *
     * @return array|false
     * @throws \Exception
     */
    public function fetch($query = '', $args = [])
    {
        $stmt = $this->querying($query, $args);
        if ($stmt) {
            $ret = $stmt->get_result();
            if ($ret !== false && $ret !== null) {
                $tuple = [];
                while ($row = $ret->fetch_assoc()) {
                    $tuple[] = $row;
                }
                $ret->free_result();
                return empty($tuple) ? null : $tuple;
            }
        }
        return false;
    }

    /**
     * method for the purpose of executing queries select for count rows from the database
     *
     * @param string $query
     * @param array  $args
     *
     * @return int
     * @throws \Exception
     */
    public function count($query = '', $args = [])
    {
        $stmt = $this->querying($query, $args);
        if ($stmt) {
            $ret = $stmt->get_result();
            if ($ret) {
                $rows = $ret->num_rows;
                $ret->close();
                $stmt->free_result();
                return $rows;
            }
        }
        return -1;
    }

    /**
     * describe table in database and return it as formated array
     *
     * @param $tableName
     *
     * @return array
     * @throws \Exception
     */
    public function describe($tableName)
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->describe($tableName), []);
        return CurrentDB::$current->queryStringHandler->describeMiddleware($result);
    }

    /**
     * show all tables from selected database
     *
     * @return array
     * @throws \Exception
     */
    public function show()
    {
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->show(), []);
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