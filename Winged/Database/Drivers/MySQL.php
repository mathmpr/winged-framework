<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use Winged\Utils\Chord;
use WingedConfig;

/**
 * Syntax compatible with PostgreSQL 10.0.2
 * Class PostgreSQL
 *
 * @package Winged\Database\Drivers
 */
class MySQL extends Eloquent implements EloquentInterface
{

    public $initialDelete = 'DELETE';

    public $initialUpdate = 'UPDATE';

    public $initialSelect = 'SELECT';

    public $initialInsert = 'INSERT INTO';

    public $modifiersConditions = [
        ELOQUENT_DIFFERENT => '<>',
        ELOQUENT_SMALLER => '<',
        ELOQUENT_LARGER => '>',
        ELOQUENT_SMALLER_OR_EQUAL => '<=',
        ELOQUENT_LARGER_OR_EQUAL => '>=',
        ELOQUENT_EQUAL => '=',
        ELOQUENT_BETWEEN => 'BETWEEN',
        ELOQUENT_DESC => 'DESC',
        ELOQUENT_ASC => 'ASC',
        ELOQUENT_IN => 'IN',
        ELOQUENT_NOTIN => 'NOT IN',
        ELOQUENT_LIKE => 'LIKE',
        ELOQUENT_NOTLIKE => 'NOT LIKE',
        ELOQUENT_IS_NULL => 'IS NULL',
        ELOQUENT_IS_NOT_NULL => 'IS NOT NULL'
    ];

    public function setEncoding()
    {
        CurrentDB::execute('SET NAMES ' . WingedConfig::$config->db()->DATABASE_CHARSET);
    }

    /**
     * Return query string for show tables in current database
     *
     * @return string
     */
    public function show()
    {
        return "SHOW TABLES";
    }

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function showMiddleware($fields = [])
    {
        $clear_fields = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $clear_fields[$field['Tables_in_' . $this->database->dbname]] = [
                    'table_name' => $field['Tables_in_' . $this->database->dbname],
                ];
            }
        }
        return $clear_fields;
    }

    /**
     * Return query to fetch table information in current database
     *
     * @param string $tableName
     *
     * @return string
     */
    public function describe($tableName = '')
    {
        return "DESC " . $tableName;
    }

    /**
     * parse results into a formated results to database core
     *
     * @param array $fields
     *
     * @return array
     */
    public function describeMiddleware($fields = [])
    {
        $clear_fields = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $clear_fields[$field['Field']] = [
                    'field' => $field['Field'],
                    'not_null' => $field['Null'] == 1 ? true : false,
                    'default' => $field['Default'],
                    'pk' => $field['Key'] === "PRI" ? true : false,
                    'type' => trim(preg_replace("/\([^)]+\)/", '', $field['Type'])),
                    'extra' => $field['Extra']
                ];
            }
        }
        return $clear_fields;
    }


    public function parseQuery()
    {
        switch ($this->command) {
            case Eloquent::COMMAND_INSERT:
                $this->currentQueryString .= $this->initialInsert;
                break;
            case Eloquent::COMMAND_DELETE:
                $this->currentQueryString .= $this->initialDelete;
                break;
            case Eloquent::COMMAND_UPDATE:
                $this->currentQueryString .= $this->initialUpdate;
                break;
            case Eloquent::COMMAND_SELECT:
                $this->currentQueryString .= $this->initialSelect;
                if ($this->distinct) {
                    $this->currentQueryString .= ' DISTINCT';
                }
                break;
            default:
                break;
        }
        return $this;
    }

    public function parseJoin()
    {
        $part = '';
        $masterKey = 'joins';
        foreach ($this->queryTablesInfo[$masterKey] as $key => $join) {
            $part .= strtoupper($join['original']['type']) . ' JOIN ';

            if ($this->queryTablesAlias[$masterKey][$key]) {
                $part .= $this->queryTablesAlias[$masterKey][$key] . ' AS ' . $this->queryTables[$masterKey][$key];
            } else {
                $part .= $this->queryTables[$masterKey][$key];
            }

            $part .= ' ON ';

            if ($join['left']['alias']) {
                $part .= $join['left']['alias'] . '.' . $join['left']['field'];
            } else {
                $part .= $join['left']['table'] . '.' . $join['left']['field'];
            }

            if ($join['right']['alias']) {
                $part .= ' ' . $join['condition'] . ' ' . $join['right']['alias'] . '.' . $join['right']['field'];
            } else {
                $part .= ' ' . $join['condition'] . ' ' . $join['right']['table'] . '.' . $join['right']['field'];
            }
        }
        $this->currentQueryString .= ' ' . $part;
        return $this;
    }

    public function parseWhere()
    {
        $part = '';
        $masterKey = 'where';
        $parenthesis = 0;
        if (count7($this->queryTablesInfo[$masterKey]) > 0) {
            foreach ($this->queryTablesInfo[$masterKey] as $key => $where) {
                $keys = array_keys($where['original']['args']);
                if ($where['original']['type'] === 'begin') {
                    $part .= ' WHERE (' . $keys[0] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s';
                }
                if ($where['original']['type'] === 'and') {
                    $part .= ' AND (' . $keys[0] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s';
                    $parenthesis++;
                }
                if ($where['original']['type'] === 'or') {
                    $next = $this->afterClauseOperation($key, $masterKey);
                    if ($next) {
                        if ($next == 'or') {
                            $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s';
                        } else {
                            if ($parenthesis > 0) {
                                $parenthesis--;
                            }
                            $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s )';
                        }
                    } else {
                        if ($parenthesis > 0) {
                            $parenthesis--;
                        }
                        $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s )';
                    }
                }
                $this->pushQueryInformation($where['left'], $where['right']);
            }
            for ($x = 0; $x < $parenthesis; $x++) {
                $part .= ')';
            }
            $this->currentQueryString .= ' ' . $part . ')';
        }
        return $this;
    }

    public function parseGroup()
    {
        $part = 'GROUP BY ';
        $masterKey = 'groupBy';
        foreach ($this->queryFields[$masterKey] as $key => $field) {
            if ($this->queryFieldsAlias[$masterKey][$key]) {
                $part .= $this->queryTablesAlias[$masterKey][$key] . '.' . $field . ' AS ' . $this->queryFieldsAlias[$masterKey][$key] . ',';
            } else {
                $part .= $this->queryTablesAlias[$masterKey][$key] . '.' . $field . ',';
            }
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }


    /**
     * @return $this|EloquentInterface
     * @throws \Exception
     */
    public function parseHaving()
    {
        $part = '';
        $masterKey = 'having';
        $parenthesis = 0;
        if (count7($this->queryTablesInfo[$masterKey]) > 0) {
            foreach ($this->queryTablesInfo[$masterKey] as $key => $having) {
                $keys = array_keys($having['original']['args']);
                if ($having['original']['type'] === 'begin') {
                    $part .= ' HAVING (' . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s';
                }
                if ($having['original']['type'] === 'and') {
                    $part .= ' AND (' . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s';
                    $parenthesis++;
                }
                if ($having['original']['type'] === 'or') {
                    $next = $this->afterClauseOperation($key, $masterKey);
                    if ($next) {
                        if ($next == 'or') {
                            $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s';
                        } else {
                            if ($parenthesis > 0) {
                                $parenthesis--;
                            }
                            $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s )';
                        }
                    } else {
                        if ($parenthesis > 0) {
                            $parenthesis--;
                        }
                        $part .= ' OR ' . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s )';
                    }
                }
                $this->pushQueryInformation($having['left'], $having['right']);
            }
            for ($x = 0; $x < $parenthesis; $x++) {
                $part .= ')';
            }
            $this->currentQueryString .= ' ' . $part . ')';
        }
        return $this;
    }

    public function parseOrder()
    {
        $part = 'ORDER BY ';
        $masterKey = 'orderBy';
        foreach ($this->queryTablesInfo[$masterKey] as $key => $orderBy) {
            $part .= $orderBy['original'][1] . ' ' . $orderBy['original'][0] . ',';
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }

    public function parseSet()
    {
        $part = 'SET ';
        $masterKey = 'set';
        foreach ($this->queryFields[$masterKey] as $key => $fieldName) {
            $part .= $fieldName . ' = %s,';
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }

    public function parseValues()
    {
        return $this;
    }

    public function parseDelete()
    {
        return $this;
    }

    public function parseUpdate()
    {
        $part = '';
        $masterKey = 'update';
        foreach ($this->queryTables[$masterKey] as $key => $value) {
            if ($this->queryTablesAlias[$masterKey][$key]) {
                $part .= $this->queryTablesAlias[$masterKey][$key] . ' AS ' . $value . ',';
            } else {
                $part .= $value . ',';
            }
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }

    public function parseLimit()
    {
        if (!empty($this->limit)) {
            if ($this->limit['final'] === 0) {
                $this->queryValues[] = $this->limit['init'];
                $this->pdoDataType[] = ':init_limit';
                $this->mysqliDataType[] = 'i';
                $this->currentQueryString .= ' LIMIT %s';
            } else {
                $this->queryValues[] = $this->limit['init'];
                $this->queryValues[] = $this->limit['final'];
                $this->pdoDataType[] = ':init_limit';
                $this->pdoDataType[] = ':final_limit';
                $this->mysqliDataType[] = 'i';
                $this->mysqliDataType[] = 'i';
                $this->currentQueryString .= ' LIMIT %s, %s';

            }
        }
        return $this;
    }

    public function parseSelect()
    {
        $part = '';
        $masterKey = 'select';
        foreach ($this->queryFields[$masterKey] as $key => $field) {
            if ($this->queryTablesAlias[$masterKey][$key]) {
                if ($this->queryFieldsAlias[$masterKey][$key]) {
                    $part .= $this->queryTablesAlias[$masterKey][$key] . '.' . $field . ' AS ' . $this->queryFieldsAlias[$masterKey][$key] . ',';
                } else {
                    $part .= $this->queryTablesAlias[$masterKey][$key] . '.' . $field . ',';
                }
            } else {
                if ($this->queryFieldsAlias[$masterKey][$key]) {
                    $part .= $field . ' AS ' . $this->queryFieldsAlias[$masterKey][$key] . ',';
                } else {
                    $part .= $field . ',';
                }
            }
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }

    /**
     * @return $this|EloquentInterface
     */
    public function parseFrom()
    {
        $part = '';
        $masterKey = 'from';
        foreach ($this->queryTables[$masterKey] as $key => $value) {
            if ($this->queryTablesAlias[$masterKey][$key]) {
                $part .= $this->queryTablesAlias[$masterKey][$key] . ' AS ' . $value . ',';
            } else {
                $part .= $value . ',';
            }
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' FROM ' . $part->get();
        return $this;
    }

    /**
     * @return $this|EloquentInterface
     * @throws \Exception
     */
    public function prepare()
    {
        if ($this->builded) {
            $this->resetAll();
        }
        if ($this->prepared) {
            $this->reset();
        }
        $this->prepared = true;
        $this->parseQuery();
        switch ($this->command) {
            case Eloquent::COMMAND_INSERT:

                break;
            case Eloquent::COMMAND_DELETE:

                break;
            case Eloquent::COMMAND_UPDATE:
                try {
                    $this->parseTables('update')
                        ->parseTables('joins');
                } catch (\Exception $exception) {
                    pre_clear_buffer_die($exception->getMessage());
                    return $this;
                }

                try {
                    $this->parseFields('set');
                    $this->parseFields('where');
                } catch (\Exception $exception) {
                    return $this;
                }

                $this->parseUpdate()
                    ->parseSet()
                    ->parseJoin()
                    ->parseWhere();
                break;
            case Eloquent::COMMAND_SELECT:
                try {
                    $this->parseTables('from')
                        ->parseTables('joins');
                } catch (\Exception $exception) {
                    return $this;
                }
                try {
                    $this->parseFields('select')
                        ->parseFields('groupBy')
                        ->parseFields('orderBy')
                        ->parseFields('having')
                        ->parseFields('where');
                } catch (\Exception $exception) {
                    return $this;
                }
                $this->parseSelect()
                    ->parseFrom()
                    ->parseJoin()
                    ->parseWhere()
                    ->parseGroup()
                    ->parseHaving()
                    ->parseOrder()
                    ->parseLimit();
                break;
            default:
                break;
        }
        while (is_int(stripos($this->currentQueryString, '  '))) {
            $this->currentQueryString = str_replace('  ', ' ', $this->currentQueryString);
        }
        return $this;
    }

    /**
     * @return void|EloquentInterface
     * @throws \Exception
     */
    public function build()
    {
        $this->prepared = false;
        $this->builded = true;
    }
}