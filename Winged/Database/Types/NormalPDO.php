<?php

namespace Winged\Database\Types;

use Winged\Database\CurrentDB;

/**
 * used when \WingedDatabaseConfig object->$USE_PREPARED_STMT = NO_USE_PREPARED_STMT and \WingedDatabaseConfig object->$STD_DB_CLASS = IS_PDO
 *
 * Class NormalPDO
 *
 * @package Winged\Database\Types
 */
class NormalPDO
{

    /** @var $refer \PDO */
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
     * NormalPDO constructor.
     *
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->refer = $db;
    }

    /**
     * prepare and execute any query, return resolved stmt after
     *
     * @param $query
     *
     * @return bool|\PDOStatement
     * @throws \Exception
     */
    private function querying($query)
    {
        $stmt = $this->refer->prepare($query);
        if ($stmt === false) {
            throw new \Exception("DB error: can't prepare query - " . $query . ' : ' . $this->refer->errorInfo()[2]);
        } else {
            $ret = false;
            try {
                $ret = $stmt->execute();
            } catch (\PDOException $error) {
                trigger_error("DB error: " . $error->getMessage());
            }
            if ($ret) {
                $this->last_stmt = $stmt;
                $this->affected_rows = $stmt->rowCount();
                $this->sqlstate = $this->refer->errorInfo()[0];
                $this->error_code = $this->refer->errorInfo()[1];
                $this->error_info = $this->refer->errorInfo()[2];
                return $stmt;
            } else {
                $this->affected_rows = 0;
                $this->sqlstate = '';
                $this->error_code = 0;
                $this->error_info = '';
            }
        }
        return false;
    }

    /**
     * method for the purpose of executing queries to delete ou update data in the database
     *
     * @param string $query
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($query = '')
    {
        $stmt = $this->querying($query);
        if ($stmt) {
            return true;
        }
        return false;
    }

    /**
     * method for the purpose of executing queries to retrieve data from the database
     *
     * @param string $query
     *
     * @return int|false
     * @throws \Exception
     */
    public function insert($query = '')
    {
        $stmt = $this->querying($query);
        if ($stmt) {
            return $this->refer->lastInsertId();
        }
        return false;
    }

    /**
     * method for the purpose of executing queries to retrieve data from the database
     *
     * @param string $query
     *
     * @return array|false
     * @throws \Exception
     */
    public function fetch($query = '')
    {
        $stmt = $this->querying($query);
        if ($stmt) {
            $all = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return empty($all) || $all === false ? null : $all;
        }
        return false;
    }

    /**
     * method for the purpose of executing queries select for count rows from the database
     *
     * @param string $query
     *
     * @return int
     * @throws \Exception
     */
    public function count($query = '')
    {
        $stmt = $this->querying($query);
        if ($stmt) {
            return $stmt->rowCount();
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
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->describe($tableName));
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
        $result = $this->fetch(CurrentDB::$current->queryStringHandler->show());
        return CurrentDB::$current->queryStringHandler->showMiddleware($result);
    }

    /**
     * close this conection
     */
    public function close()
    {
        $this->refer = null;
    }

}