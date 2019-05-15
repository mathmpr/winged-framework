<?php

namespace Winged\Database\Drivers;

use Winged\Database\Database;
use Winged\Model\Model;

/**
 * Class Eloquent
 *
 * @package Winged\Database\Drivers
 */
abstract class Eloquent
{
    const COMMAND_DELETE = 'delete';

    const COMMAND_UPDATE = 'update';

    const COMMAND_SELECT = 'select';

    const COMMAND_INSERT = 'insert';

    public $queryAlias = false;

    public $currentQueryString = '';

    public $queryTables = [];

    public $queryFields = [];

    public $queryTablesAlias = [];

    public $queryFieldsAlias = [];

    public $queryInfo = [];

    public $initialDelete = 'DELETE';

    public $initialUpdate = 'UPDATE';

    public $initialSelect = 'SELECT';

    public $initialInsert = 'INSERT';

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

    /**
     * @var $database null | Database
     */
    protected $database = null;

    /**
     * store an model, if an models is stored here, the intire behavior of this class change
     *
     * @var $model null | Model
     */
    protected $model = null;

    /**
     * store fields for select statement
     *
     * @var $select array
     */
    protected $select = [];

    /**
     * store main table name and main alias for select statement
     *
     * @var $from array
     */
    protected $from = [];

    /**
     * store configs for inner join
     *
     * @var $innerJoin array
     */
    protected $joins = [];

    /**
     * store having values
     *
     * @var $having array
     */
    protected $having = [];

    /**
     * store grouped fields
     *
     * @var $groupBy array
     */
    protected $groupBy = [];

    /**
     * set if query fetch data has distinct
     *
     * @var $distinct array
     */
    protected $distinct = false;

    /**
     * set order by field and direction
     *
     * @var $orderBy array
     */
    protected $orderBy = [];

    /**
     * set limit when query fetch data
     *
     * @var $limit array
     */
    protected $limit = [];

    /**
     * store alias for tables
     *
     * @var $alias array
     */
    protected $alias = [];

    /**
     * store fields and values for insert statement
     *
     * @var $into array
     */
    protected $into = [];

    /**
     * store tables names for update statement
     *
     * @var $update array
     */
    protected $update = [];

    /**
     * store tables names for insert statement
     *
     * @var $insert array
     */
    protected $insert = [];

    /**
     * store fields and values for delete statement
     *
     * @var $delete array
     */
    protected $delete = [];

    /**
     * store fields and values for update statement
     *
     * @var $set array
     */
    protected $set = [];

    /**
     * store other eloquents objects, when main eloquent calls build, the others eloquents are buld so
     *
     * @var $eloquents Eloquent[] | Sqlite[] | SQLServer[] | PostgreSQL[] | MySQL[] | Cubrid[]
     */
    protected $eloquents = [];

    /**
     * store alias for tables
     *
     * @var $alias array
     */
    protected $values = [];

    /**
     * store where statement for query
     *
     * @var $where array
     */
    protected $where = [];

    protected $command = false;

    /**
     * Eloquent constructor.
     *
     * @param Database     $database
     * @param Model | null $model
     */
    public function __construct($database, $model = null)
    {
        $this->database = $database;
        $this->model = $model;
        if (!$model) {

        }
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function select($fields = [])
    {
        $this->command = Eloquent::COMMAND_SELECT;
        $this->storeArray($fields, 'select');
        return $this;
    }

    /**
     * Adds distinct clause in query
     *
     * @param bool $boolean
     *
     * @return $this
     */
    public function distinct($boolean = true)
    {
        $this->distinct = $boolean;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function from($tables = [])
    {
        $this->storeArray($tables, 'from');
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function update($tables = [])
    {
        $this->command = Eloquent::COMMAND_UPDATE;
        $this->storeArray($tables, 'update');
        return $this;
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function set($fields = [])
    {
        $this->storeArray($fields, 'set');
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function delete($tables = [])
    {
        $this->command = Eloquent::COMMAND_DELETE;
        $this->storeArray($tables, 'delete');
        return $this;
    }

    /**
     * @return $this
     */
    public function insert()
    {
        $this->command = Eloquent::COMMAND_INSERT;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function into($tables = [])
    {
        $this->storeArray($tables, 'into');
        return $this;
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function values($fields = [])
    {
        $this->storeArray($fields, 'values');
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function where($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'begin', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function andWhere($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'and', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function orWhere($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'or', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Adds limit clause to query
     *
     * @param int $initial_or_count
     * @param int $final
     *
     * @return $this
     */
    public function limit($initial_or_count, $final = 0)
    {
        $initial_or_count = intval($initial_or_count);
        $final = intval($final);

        if (is_int($initial_or_count) && is_int($final)) {
            $this->limit = [
                'init' => $initial_or_count,
                'final' => $final
            ];
        }

        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     *
     * @param array  $inner
     * @param string $condition
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function leftJoin($inner = [], $condition = '')
    {
        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'left', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     *
     * @param array  $inner
     * @param string $condition
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function rightJoin($inner = [], $condition = '')
    {

        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'right', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name'], 'alias.field_name = alias.field_name'
     *
     * @param array  $inner
     * @param string $condition
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function innerJoin($inner = [], $condition = '')
    {
        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'inner', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Adds order by clause in query
     *
     * @param string $direction
     * @param string $field
     *
     * @return $this
     */
    public function orderBy($direction = '', $field = '')
    {
        $direction = trim($direction);
        if ($direction == ELOQUENT_ASC || $direction == ELOQUENT_DESC) {
            $this->orderBy[] = [
                $direction,
                $field
            ];
        }
        return $this;
    }

    /**
     * set groupBy clause in query with fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function groupBy($fields = [])
    {
        $this->storeArray($fields, 'groupBy');
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function having($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'begin', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function andHaving($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'and', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function orHaving($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'or', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * set tables names into property name if property exists in self object
     *
     * @param $args
     * @param $propertyName
     */
    protected function storeArray($args, $propertyName)
    {
        if (property_exists($this, $propertyName)) {
            $this->{$propertyName} = $args;
        }
    }

    /**
     * add where, having and join clause into query
     *
     * @param string $condition
     * @param array  $args
     * @param string $command
     * @param string $propertyName
     *
     * @throws \Exception
     *
     * @return false | $this
     */
    protected function parse($condition = '', $args = [], $command = 'begin', $propertyName = 'where')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }

        $countArguments = count7($args);
        if ($countArguments >= 2) {
            throw new \Exception('args inside $args expected exactly two parameters, given ' . (is_bool($countArguments) ? 'boolean value' : $countArguments));
        }

        if (($command === 'or' || $command === 'and') && empty($this->{$propertyName})) {
            $command = 'begin';
        }

        if (!empty($this->{$propertyName}) && $command === 'begin') {
            $command = 'and';
        }

        if (!in_array($command, ['begin', 'and', 'or', 'inner', 'left', 'right'])) {
            $command = 'begin';
        }

        $this->{$propertyName}[] = [
            'type' => $command,
            'condition' => $condition,
            'args' => $args,
        ];

        return $this;
    }

    /**
     * check if field exists inside an table
     *
     * @param string $string
     *
     * @return bool
     */
    protected function fieldExists($string = '')
    {
        if ($this->model) {
            return in_array($string, $this->model->tableFields());
        }
        return true;
    }

    /**
     * check if table exists in database
     *
     * @param string $string
     *
     * @return bool
     */
    protected function tableExists($string = '')
    {
        return array_key_exists($string, $this->database->db_tables);
    }


}