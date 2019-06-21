<?php

namespace Winged\Database\Drivers;

/**
 * Interface EloquentInterface
 *
 * @package Winged\Database\Drivers
 */
interface EloquentInterface
{

    /**
     * set default encoding to client conection
     */
    public function setEncoding();

    /**
     * Return query string for show tables in current database
     *
     * @return string
     */
    public function show();

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function showMiddleware($fields = []);

    /**
     * Return query to fetch table information in current database
     *
     * @param string $tableName
     *
     * @return string
     */
    public function describe($tableName = '');

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function describeMiddleware($fields = []);

    /**
     * get initial query for the selected command
     *
     * @return $this|EloquentInterface
     */
    public function parseQuery();

    /**
     * adds join clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseJoin();

    /**
     * adds where clause on $this->currentQueryString
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function parseWhere();

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseGroup();

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function parseHaving();

    /**
     * adds order by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseOrder();

    /**
     * adds set clause for update queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseSet();

    /**
     * adds group by clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseValues();

    /**
     * adds limit clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseLimit();

    /**
     * adds table names for select queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseFrom();

    /**
     * adds names of field for select queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseSelect();

    /**
     * adds delete and from clause on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseDelete();

    /**
     * adds updated tables in update clase on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseUpdate();

    /**
     * adds values for insert queries on $this->currentQueryString
     *
     * @return $this|EloquentInterface
     */
    public function parseInto();

    /**
     * prepare any query for after build query and execute then
     *
     * @throws \Exception
     *
     * @return $this|EloquentInterface
     */
    public function prepare();
}