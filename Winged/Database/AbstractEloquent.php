<?php

namespace Winged\Database;

use Winged\Database\Drivers\Cubrid;
use Winged\Database\Drivers\MySQL;
use Winged\Database\Drivers\PostgreSQL;
use Winged\Database\Drivers\Sqlite;
use Winged\Database\Drivers\SQLServer;
use Winged\External\PHPMailer\PHPMailer\Exception;
use Winged\WingedConfig;

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
        try {
            $reflection = new \ReflectionClass(get_class(CurrentDB::$current->queryStringHandler));
            $this->eloquent = $reflection->newInstanceArgs([CurrentDB::$current, $this]);
        } catch (\ReflectionException $exception) {
            return false;
        }
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
        $this->eloquent->insert();
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
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
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
     * @return $this
     */
    public function andWhere($condition = '', $values = [])
    {
        try {
            $this->eloquent->andWhere($condition, $values);
        } catch (\Exception $exception) {
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
     * @return $this
     */
    public function orWhere($condition = '', $values = [])
    {
        try {
            $this->eloquent->orWhere($condition, $values);
        } catch (\Exception $exception) {
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
        $this->eloquent->limit($initial_or_count, $final);
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
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
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
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
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
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
            return $this;
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
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
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
            $this->eloquent->andHaving($condition, $values);
        } catch (\Exception $exception) {
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
            $this->eloquent->orHaving($condition, $values);
        } catch (\Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * perform query building
     *
     * @return Cubrid|Drivers\EloquentInterface|MySQL|PostgreSQL|Sqlite|SQLServer|$this
     */
    public function build()
    {
        try {
            return $this->eloquent->build();
        } catch (\Exception $exception) {
            return $this;
        }
    }

    /**
     * perform an pre build query
     *
     * @return Cubrid|Drivers\EloquentInterface|MySQL|PostgreSQL|Sqlite|SQLServer|$this
     *
     */
    public function prepare()
    {
        try {
            return $this->eloquent->prepare();
        } catch (\Exception $exception) {
            return $this;
        }
    }

    /**
     * execute and eloquent object if eloquent has prepared and builded
     *
     * @return array|bool|mixed|string
     */
    public function execute()
    {
        $returnedValue = false;
        if ($this->eloquent->builded) {
            switch ($this->eloquent->builded['command']) {
                case Eloquent::COMMAND_INSERT:
                    if ($this->eloquent->database->isPdo() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->insert($this->eloquent->builded['pdo_query'], $this->eloquent->builded['pdo']);
                    } else if ($this->eloquent->database->isMysqli() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->insert($this->eloquent->builded['mysqli_query'], $this->eloquent->builded['mysqli']);
                    } else {
                        $returnedValue = $this->eloquent->database->insert($this->eloquent->builded['query']);
                    }
                    break;
                case Eloquent::COMMAND_DELETE:
                    if ($this->eloquent->database->isPdo() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['pdo_query'], $this->eloquent->builded['pdo']);
                    } else if ($this->eloquent->database->isMysqli() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['mysqli_query'], $this->eloquent->builded['mysqli']);
                    } else {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['query']);
                    }
                    break;
                case Eloquent::COMMAND_UPDATE:
                    if ($this->eloquent->database->isPdo() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['pdo_query'], $this->eloquent->builded['pdo']);
                    } else if ($this->eloquent->database->isMysqli() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['mysqli_query'], $this->eloquent->builded['mysqli']);
                    } else {
                        $returnedValue = $this->eloquent->database->execute($this->eloquent->builded['query']);
                    }
                    break;
                case Eloquent::COMMAND_SELECT:
                    if ($this->eloquent->database->isPdo() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->fetch($this->eloquent->builded['pdo_query'], $this->eloquent->builded['pdo']);
                    } else if ($this->eloquent->database->isMysqli() && WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $returnedValue = $this->eloquent->database->fetch($this->eloquent->builded['mysqli_query'], $this->eloquent->builded['mysqli']);
                    } else {
                        $returnedValue = $this->eloquent->database->fetch($this->eloquent->builded['query']);
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }
        $this->eloquent->unbuild();
        return $returnedValue;
    }
}