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
        return $this;
    }

    public function parseHaving()
    {
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
     * @param string $propertyName
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function parseFields($propertyName = '')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }
        foreach ($this->{$propertyName} as $key => $property) {

            if (!array_key_exists($propertyName, $this->queryFields)) {
                $this->queryFields[$propertyName] = [];
                $this->queryFieldsAlias[$propertyName] = [];
            }

            if (is_array($property)) {
                if ($propertyName === 'where') {
                    $keys = array_keys($property['args']);
                    $information = $this->getInformation($keys[0]);
                    $theValue = $this->normalizeValue($property['args'][$keys[0]], $information['table'], $information['field']);
                    if (!is_string($keys[0])) {
                        throw new \Exception('error in where clause, check if sintax are correctly. first params is condition, and second is an associative array where key is an string and any value after');
                    }
                    if (($property['condition'] === ELOQUENT_IN || $property['condition'] === ELOQUENT_NOTIN) &&
                        !is_array($theValue)) {
                        throw new \Exception('error in where clause, in condition requires value has array');
                    }
                    if ($property['condition'] === ELOQUENT_BETWEEN &&
                        !is_array($theValue) &&
                        count7($theValue) != 2) {
                        throw new \Exception('error in where clause, between condition requires value has array and array count exactly equals two');
                    }
                    $info = [
                        'condition' => $property['condition'],
                        'original' => $property,
                        'left' => $information,
                        'right' => $theValue,
                        'type' => 'where',
                    ];
                    $this->queryTablesInfo[$propertyName][] = $info;
                }
            } else {
                if (is_string($key)) {
                    $realName = $this->getInformation($key);
                    $this->queryFieldsAlias[$propertyName][] = $property;
                    $this->queryFields[$propertyName][] = $realName['field'];
                    $this->queryTables[$propertyName][] = $realName['table'];
                    $this->queryTablesAlias[$propertyName][] = $realName['alias'];
                } else {
                    $realName = $this->getInformation($property);
                    $this->queryFieldsAlias[$propertyName][] = false;
                    $this->queryTables[$propertyName][] = $realName['table'];
                    $this->queryTablesAlias[$propertyName][] = $realName['alias'];
                    $this->queryFields[$propertyName][] = $realName['field'];
                }
            }
        }
        return $this;
    }

    /**
     * @param string $propertyName
     *
     * @throws \Exception
     *
     * @return $this;
     */
    public function parseTables($propertyName = '')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }
        foreach ($this->{$propertyName} as $key => $property) {

            if (!array_key_exists($propertyName, $this->queryTables)) {
                $this->queryTables[$propertyName] = [];
                $this->queryTablesAlias[$propertyName] = [];
                $this->queryTablesInfo[$propertyName] = [];
            }
            $info = false;
            $alias = false;
            if (is_array($property)) {
                switch ($propertyName) {
                    case 'joins':
                        $condition = str_replace(' ', '', $property['condition']);
                        $keys = array_keys($property['args']);
                        if (is_string($keys[0])) {
                            $alias = $keys[0];
                        }
                        $tableName = $property['args'][$keys[0]];
                        $this->queryTables[$propertyName][] = $tableName;
                        $this->queryTablesAlias[$propertyName][] = $alias;
                        if (!is_string($keys[1])) {
                            throw new \Exception('in join clause is required an string key with field or table.field or alias.field');
                        }
                        $leftInfo = $this->getInformation($keys[1]);
                        $rightInfo = $this->getInformation($property['args'][$keys[1]]);
                        $info = [
                            'condition' => $condition,
                            'original' => $property,
                            'left' => $leftInfo,
                            'right' => $rightInfo,
                            'type' => 'joins',
                        ];
                        $this->queryTablesInfo[$propertyName][] = $info;
                        break;
                    default:
                        break;
                }
            } else {
                if (is_string($key)) {
                    $alias = $key;
                    if (in_array($alias, $this->queryTablesAlias[$propertyName])) {
                        throw new \Exception('can\'t use an table alias twice. the duplicated alias is: ' . $alias);
                    }
                }
                $tableName = $property;
                if (!$this->tableExists($tableName)) {
                    throw new \Exception('table ' . $tableName . ' do not exists in database ' . $this->database->dbname);
                }

                if (in_array($tableName, $this->queryTables[$propertyName])) {
                    throw new \Exception('can\'t use name of table twice. the name of table is: ' . $tableName);
                }
                $this->queryTables[$propertyName][] = $tableName;
                $this->queryTablesAlias[$propertyName][] = $alias;
                $this->queryTablesInfo[$propertyName][] = $info;
            }
        }
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
                break;
            default:
                break;
        }
    }
}