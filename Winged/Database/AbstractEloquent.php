<?php

namespace Winged\Database;

use Winged\Database\Drivers\Cubrid;
use Winged\Database\Drivers\EloquentInterface;
use Winged\Database\Drivers\MySQL;
use Winged\Database\Drivers\PostgreSQL;
use Winged\Database\Drivers\Sqlite;
use Winged\Database\Drivers\SQLServer;
use Winged\Model\Model;
use WingedConfig;

/**
 * Class AbstractEloquent
 *
 * @package Winged\Database
 */
class AbstractEloquent
{
    /**
     * @var $eloquent null | Sqlite | MySQL | Cubrid | SQLServer | PostgreSQL
     */
    public $eloquent = null;

    public function __construct()
    {
        $this->eloquent = &CurrentDB::$current->queryStringHandler;
        return $this;
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
        if (empty($fields)) {
            $fields = ['*'];
        }
        $this->eloquent->select($fields);
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
        $this->eloquent->distinct($boolean);
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
        $this->eloquent->from($tables);
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
        $this->eloquent->update($tables);
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
        $this->eloquent->set($fields);
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
        $this->eloquent->delete($tables);
        return $this;
    }

    /**
     * @return $this
     */
    public function insert()
    {
        try {
            $this->eloquent->insert();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function into($table = '')
    {
        $this->eloquent->into($table);
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
        $this->eloquent->values($fields);
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     */
    public function where($condition = '', $values = [])
    {
        try {
            $this->eloquent->where($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     */
    public function andWhere($condition = '', $values = [])
    {
        try {
            $this->eloquent->andWhere($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     */
    public function orWhere($condition = '', $values = [])
    {
        try {
            $this->eloquent->orWhere($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
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
        $this->eloquent->limit($initial_or_count, $final);
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     */
    public function leftJoin($condition = '', $inner = [])
    {
        try {
            $this->eloquent->leftJoin($condition, $inner);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     */
    public function rightJoin($condition = '', $inner = [])
    {
        try {
            $this->eloquent->rightJoin($condition, $inner);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     */
    public function innerJoin($condition = '', $inner = [])
    {
        try {
            $this->eloquent->innerJoin($condition, $inner);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
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
        $this->eloquent->orderBy($direction, $field);
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
        $this->eloquent->groupBy($fields);
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     */
    public function having($condition = '', $values = [])
    {
        try {
            $this->eloquent->having($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function andHaving($condition = '', $values = [])
    {
        try {
            $this->eloquent->andHaving($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Exemple: ELOQUENT_EQUAL, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function orHaving($condition = '', $values = [])
    {
        try {
            $this->eloquent->orHaving($condition, $values);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * perform query building
     * reset prepared infos and all registred commands in query
     *
     * @return Cubrid|Drivers\EloquentInterface|MySQL|PostgreSQL|Sqlite|SQLServer|$this
     */
    public function build()
    {
        $this->eloquent->model = &$this;
        try {
            return $this->eloquent->build();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * perform query building
     * reset prepared infos and all registred commands in query
     *
     * @return Cubrid|Drivers\EloquentInterface|MySQL|PostgreSQL|Sqlite|SQLServer|$this
     */
    public function buildKeepRegisters()
    {
        $this->eloquent->model = &$this;
        try {
            return $this->eloquent->build(true);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * perform an pre build query
     *
     * @return Cubrid|Drivers\EloquentInterface|MySQL|PostgreSQL|Sqlite|SQLServer|$this
     *
     */
    public function prepare()
    {
        $this->eloquent->model = &$this;
        try {
            return $this->eloquent->prepare();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $this;
    }

    /**
     * execute and eloquent object if eloquent has prepared and builded
     *
     * @param bool $selectAsArray
     *
     * @return array|bool|mixed|string|Model[]
     */
    public function execute($selectAsArray = false)
    {
        $this->eloquent->model = &$this;
        $return = null;
        try {
            $return = $this->eloquent->execute($selectAsArray);
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage() . '. In file ' . $exception->getFile() . ' at line ' . $exception->getLine(), E_USER_ERROR);
        }
        return $return;
    }

    /**
     * @param bool $selectAsArray
     *
     * @return mixed|Model|null
     */
    public function one($selectAsArray = false)
    {
        $this->eloquent->model = &$this;
        $return = $this->limit(1)->execute($selectAsArray);
        if ($return) {
            return $return[0];
        }
        return null;
    }

    /**
     * method for the purpose of executing queries select for count rows from the database
     */
    public function count()
    {
        $this->eloquent->model = &$this;
        $this->buildKeepRegisters();
        $this->eloquent->builded['command'] = 'count';
        $return = $this->execute();
        if ($return) {
            return $return;
        }
        return -1;
    }

}