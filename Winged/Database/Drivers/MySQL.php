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
        foreach ($this->joins as $join) {
            if (is_array($join)) {

            }
        }
        return $this;
    }

    public function parseWhere()
    {
        foreach ($this->where as $where) {
            if (is_array($where)) {

            }
        }
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
        $this->currentQueryString .= ' ' . $part->get();
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
            if (count($property) > 1) {
                $parts = explode('.', $property);
                if (count($parts) > 1) {
                    $table = $parts[0];
                    $field = $parts[1];
                } else {
                    $field = $parts[0];
                }
            } else {

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
                $this->queryInfo[$propertyName] = [];
            }

            $info = false;
            if (is_array($property)) {
                switch ($propertyName) {
                    case 'joins':
                        $condition = str_replace(' ', '', $property['condition']);

                    default:
                        break;
                }
                pre_clear_buffer_die($property);
            } else {
                $alias = false;
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
            }

            $this->queryTables[$propertyName][] = $tableName;
            $this->queryTablesAlias[$propertyName][] = $alias;
            $this->queryInfo[$propertyName][] = $info;

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