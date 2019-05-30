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
        foreach ($this->queryTablesInfo['joins'] as $key => $join) {
            $part .= strtoupper($join['original']['type']) . ' JOIN ';

            if ($this->queryTablesAlias['joins'][$key]) {
                $part .= $this->queryTablesAlias['joins'][$key] . '.' . $this->queryTables['joins'][$key];
            } else {
                $part .= $this->queryTables['joins'][$key];
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
        $part = '(';
        foreach ($this->queryTablesInfo['where'] as $key => $where) {
            $operation = '';
            if ($where['original']['type'] === 'and') {
                $operation = ' AND ';
            }
            if ($where['original']['type'] === 'or') {
                $operation = ') OR (';
            }
            $this->pushQueryInformation($where['left'], $where['right']);
            if ($where['left']['alias']) {
                $part .= $operation . $where['left']['alias'] . '.' . $where['left']['table'] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s';
            } else {
                $part .= $operation . $where['left']['table'] . ' ' . $this->modifiersConditions[$where['condition']] . ' %s';
            }
        }
        $part .= ')';
        $this->currentQueryString .= ' WHERE' . $part;
        return $this;
    }

    public function parseGroup()
    {
        $part = 'GROUP BY ';
        foreach ($this->queryFields['groupBy'] as $key => $field) {
            if ($this->queryFieldsAlias['groupBy'][$key]) {
                $part .= $this->queryTablesAlias['groupBy'][$key] . '.' . $field . ' AS ' . $this->queryFieldsAlias['groupBy'][$key] . ',';
            } else {
                $part .= $this->queryTablesAlias['groupBy'][$key] . '.' . $field . ',';
            }
        }
        $part = Chord::factory($part);
        $part->endReplace(',');
        $this->currentQueryString .= ' ' . $part->get();
        return $this;
    }

    public function parseHaving()
    {
        $part = '(';
        $parenthesis = 0;
        foreach ($this->queryTablesInfo['having'] as $key => $having) {
            $operation = '';
            $keys = array_keys($having['original']['args']);
            if ($having['original']['type'] === 'begin') {
                $parenthesis++;
            }
            if ($having['original']['type'] === 'and') {
                $operation = ' AND (';
                $parenthesis++;
            }
            if ($having['original']['type'] === 'or') {
                if ($key > 0) {
                    if ($this->queryTablesInfo['having'][$key - 1]['original']['type'] === 'and') {
                        $operation = ' OR (';
                        $parenthesis++;
                    }
                }else{
                    $operation = ' OR ';
                }
            }
            $this->pushQueryInformation($having['left'], $having['right']);
            if ($having['left']['alias']) {
                $part .= $operation . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s';
            } else {
                $part .= $operation . $keys[0] . ' ' . $this->modifiersConditions[$having['condition']] . ' %s';
            }
            if ($having['original']['type'] === 'or') {
                if ($key + 1 < count($this->queryTablesInfo['having'])) {
                    if ($this->queryTablesInfo['having'][$key + 1]['original']['type'] !== 'or') {
                        if ($parenthesis > 0) {
                            $parenthesis--;
                        }
                        $part .= ')';
                    }
                } else {
                    if ($parenthesis > 0) {
                        $parenthesis--;
                    }
                    $part .= ')';
                }
            }
        }
        for ($x = 0; $x < $parenthesis; $x++) {
            $part .= ')';
        }
        if ($parenthesis === 0) {
            $this->currentQueryString .= ' HAVING(' . $part . ')';
        } else {
            $this->currentQueryString .= ' HAVING ' . $part;
        }

        return $this;
    }

    public function parseOrder()
    {
        return $this;
    }

    public function parseSet()
    {
        return $this;
    }

    public function parseValues()
    {
        return $this;
    }

    public function parseLimit()
    {
        return $this;
    }

    public function parseSelect()
    {
        $part = '';
        foreach ($this->queryFields['select'] as $key => $field) {
            if ($this->queryTablesAlias['select'][$key]) {
                if ($this->queryFieldsAlias['select'][$key]) {
                    $part .= $this->queryTablesAlias['select'][$key] . '.' . $field . ' AS ' . $this->queryFieldsAlias['select'][$key] . ',';
                } else {
                    $part .= $this->queryTablesAlias['select'][$key] . '.' . $field . ',';
                }
            } else {
                if ($this->queryFieldsAlias['select'][$key]) {
                    $part .= $field . ' AS ' . $this->queryFieldsAlias['select'][$key] . ',';
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
        foreach ($this->queryTables['from'] as $key => $value) {
            if ($this->queryTablesAlias['from'][$key]) {
                $part .= $this->queryTablesAlias['from'][$key] . ' AS ' . $value . ',';
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
     */
    public function build()
    {
        $this->parseQuery();
        switch ($this->command) {
            case Eloquent::COMMAND_INSERT:

                break;
            case Eloquent::COMMAND_DELETE:

                break;
            case Eloquent::COMMAND_UPDATE:

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
                        ->parseFields('limit')
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

                pre_clear_buffer_die($this->currentQueryString);

                break;
            default:
                break;
        }
    }
}