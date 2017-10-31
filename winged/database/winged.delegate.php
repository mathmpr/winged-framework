<?php

class DelegateQuery extends QueryBuilder
{

    protected $last_query_key = '';
    protected $as_array = false;
    protected $query = '';
    protected $prepared_args = [];
    protected $last_builded_query = null;
    protected $last_builded_args = null;

    protected $table_info = [];
    protected $table_fields = [];
    protected $loaded_fields = [];
    protected $safe_fields = [];
    protected $old_values = [];
    protected $change_in_last_update = false;
    protected $reverse = false;
    protected $from_db = false;
    protected $is_new = true;
    protected $old_value_loaded = false;

    public function execute()
    {
        switch ($this->command) {
            case 'select':
                return $this->fetch();
                break;

            case 'update':
                return $this->realExecute();
                break;

            case 'delete':
                return $this->realExecute();
                break;

            case 'insert':
                return $this->realInsert();
                break;

            default:
                return false;
                break;
        }
    }

    private function realInsert()
    {
        $args = $this->delegate()->getQueryInfo();
        if (!empty($args['args']) && $args['args'] != null && $args['args'] != false) {
            return CurrentDB::insert($args['query'], $args['args']);
        } else {
            return CurrentDB::insert($args['query']);
        }
    }

    private function realExecute()
    {
        $args = $this->delegate()->getQueryInfo();
        if (!empty($args['args']) && $args['args'] != null && $args['args'] != false) {
            return CurrentDB::execute($args['query'], $args['args']);
        } else {
            return CurrentDB::execute($args['query']);
        }
    }

    private function fetch()
    {
        $args = $this->delegate()->getQueryInfo();
        if (!empty($args['args']) && $args['args'] != null && $args['args'] != false) {
            return CurrentDB::fetch($args['query'], $args['args']);
        } else {
            return CurrentDB::fetch($args['query']);
        }
    }

    public function find($as_array = false)
    {
        $unic = $this->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            if ($as_array) {
                return $unic;
            }
            $models = array();
            foreach ($unic as $key => $value) {
                $models[] = $this->populateModel($this->getNewObjectType(), $value);
            }
            return $models;
        }
        return null;
    }

    public function count()
    {
        $cloned = clone $this;
        $query = $cloned->delegate()->getQueryInfo();
        $count = CurrentDB::count($query['query'], $query['args']);
        unset($cloned);
        return $count;
    }

    /**
     * @param bool $as_array
     * @return array|mixed|Model|null
     */
    public function one($as_array = false)
    {
        $this->limit(0, 1);
        $unic = $this->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            $unic = $unic[0];
            if ($as_array) {
                return $unic;
            }
            return $this->populateModel($this->getNewObjectType(), $unic);
        }
        return null;
    }

    public function orSimpleFindOne($where_arr = [], $as_array = false)
    {
        return $this->simpleFindOne($where_arr, 'or', $as_array);
    }

    public function orSimpleFindAll($where_arr = [], $as_array = false)
    {
        return $this->simpleFindAll($where_arr, 'or', $as_array);
    }

    public function andSimpleFindOne($where_arr = [], $as_array = false)
    {
        return $this->simpleFindOne($where_arr, 'and', $as_array);
    }

    public function andSimpleFindAll($where_arr = [], $as_array = false)
    {
        return $this->simpleFindAll($where_arr, 'and', $as_array);
    }

    private function simpleFindAll($where_arr = [], $type, $as_array = false)
    {
        /**
         * @var $obj Model
         * @var $this Model
         */
        $obj = $this->getNewObjectType();
        $alias = randid();

        $obj->select()
            ->from(array($alias => $obj->tN($obj)));

        $first = true;

        foreach ($where_arr as $key => $value) {
            if ($first) {
                $obj->where(DbDict::EQUAL, [$alias . '.' . $key => $value]);
            } else {
                if ($type == 'and') {
                    $obj->andWhere(DbDict::EQUAL, [$alias . '.' . $key => $value]);
                } else {
                    $obj->orWhere(DbDict::EQUAL, [$alias . '.' . $key => $value]);
                }
            }
        }

        if ($as_array) {
            $unic = $obj->fetch();
            if (count($unic) > 0 && is_array($unic)) {
                return $unic;
            }
            return null;
        }

        $unic = $obj->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            $models = array();
            foreach ($unic as $key => $value) {
                $models[] = $this->populateModel($this->getNewObjectType(), $value);
            }
            return $models;
        }
        return null;
    }

    private function simpleFindOne($where_arr = [], $type, $as_array = false)
    {
        /**
         * @var $obj Model
         * @var $this Model
         */
        $obj = $this->getNewObjectType();
        $alias = randid();
        $obj->select()
            ->from(array($alias => $obj->tN($obj)));

        $first = true;

        foreach ($where_arr as $key => $value) {
            if ($first) {
                $obj->where(DbDict::EQUAL, [$alias . '.' . $key => $value]);
            } else {
                if ($type == 'and') {
                    $obj->andWhere(DbDict::EQUAL, [$alias . '.' . $key => $value]);
                } else {
                    $obj->orWhere(DbDict::EQUAL, [$alias . '.' . $key => $value]);
                }
            }
        }

        if ($as_array) {
            $unic = $obj->fetch();
            if (count($unic) > 0 && is_array($unic)) {
                $unic = $unic[0];
                return $unic;
            }
            return null;
        }
        $unic = $obj->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            $unic = $unic[0];
            return $this->populateModel($obj, $unic);
        }
        return null;
    }

    public function in()
    {
        $token = randid();
        $this->setNextQueryName($token);
        $arr = $this->find(true);
        $info = $this->getQueryInfoByTokenName($token);
        $key = false;
        if (array_key_exists(0, $info['select_arr'])) {
            $key = $info['select_arr'][0];
            $preg = preg_split('/as/i', $key);
            if (count($preg) == 2) {
                $key = trim(array_pop($preg));
            } else {
                $exp = explode('.', $key);
                if (count($exp) == 2) {
                    $key = trim(array_pop($exp));
                }
            }
        }
        $ret = [];
        if ($arr && !empty($arr) && count($arr) > 0) {
            foreach ($arr as $index => $value) {
                $ret[] = $value[$key];
            }
        }
        return $ret;
    }

    public function findAll($as_array = false)
    {
        /**
         * @var $obj Model
         * @var $this Model
         */
        $obj = $this->getNewObjectType();
        $alias = randid();
        $obj->select()
            ->from(array($alias => $obj->tN($obj)));
        if ($as_array) {
            $unic = $obj->fetch();
            if (count($unic) > 0 && is_array($unic)) {
                return $unic;
            }
            return null;
        }

        $unic = $obj->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            $models = array();
            foreach ($unic as $key => $value) {
                $models[] = $this->populateModel($this->getNewObjectType(), $value);
            }
            return $models;
        }
        return null;
    }

    /**
     * @param $id
     * @param bool $as_array
     * @return Model|null
     */
    public function findOne($id, $as_array = false)
    {
        /**
         * @var $obj Model
         * @var $this Model
         */
        $obj = $this->getNewObjectType();
        $alias = randid();
        $obj->select()
            ->from(array($alias => $obj->tN($obj)))
            ->where(DbDict::EQUAL, array($alias . '.' . $obj->fK($obj) => $id));
        if ($as_array) {
            $unic = $obj->fetch();
            if (count($unic) > 0 && is_array($unic)) {
                $unic = $unic[0];
                return $unic;
            }
            return null;
        }
        $unic = $obj->fetch();
        if (count($unic) > 0 && is_array($unic)) {
            $unic = $unic[0];
            return $this->populateModel($obj, $unic);
        }
        return null;
    }

    protected function populateModel(Model $obj, $array)
    {

        $class_name = get_class($obj);

        $reflection = new ReflectionClass($class_name);
        $reflection_method = new ReflectionMethod($class_name, 'pushExtra');;

        foreach ($array as $key => $value) {
            $lkey = strtolower($key);
            if (property_exists($class_name, $lkey)) {
                $reflection->getProperty($lkey)->setValue($obj, $value);
                $obj->old_values[$lkey] = $value;
            } else {
                $reflection_method->invokeArgs($obj, array($lkey, $value));
            }
        }

        $obj->old_values['extras'] = $obj->extras;

        return $obj;

    }

    protected function getNewObjectType()
    {
        $clone = get_class($this);
        $obj = new $clone();
        return $obj;
    }

    /**
     * @return string
     */
    public function getQueryInfo()
    {
        $last = $this->getLastQuery();
        if ($this->clear_after) {
            $this->last_query_key = false;
        }
        $this->last_builded_query = $last['query'];
        $this->last_builded_args = $last['prepared_args'];
        return ['query' => $last['query'], 'args' => $last['prepared_args']];
    }

    /**
     * @return array|bool
     */
    public function asArray()
    {
        $last = $this->getLastQuery();
        return $this->fetch();
    }

    public function setQueryToExecute($name, $clear_after_execute = true)
    {
        if (is_string($name)) {
            if (array_key_exists($name, $this->queries)) {
                $this->execute_query = $name;
                $this->clear_after = $clear_after_execute;
            }
        }
        return $this;
    }

    public function setNextQueryName($name)
    {
        if (is_string($name)) {
            $this->last_query_key = $name;
        }
        return $this;
    }

    public function setClearFactor($bool)
    {
        if (is_bool($bool)) {
            $this->clear_after = $bool;
        }
        return $this;
    }

    public function getQueryStringByTokenName($name)
    {
        if (is_string($name)) {
            if (array_key_exists($name, $this->queries)) {
                return $this->queries[$name]['query'];
            }
        }
        return false;
    }

    public function getQueryInfoByTokenName($name)
    {
        if (is_string($name)) {
            if (array_key_exists($name, $this->queries)) {
                return $this->queries[$name];
            }
        }
        return false;
    }

    /**
     * @return array
     */
    private function saveQuery()
    {

        if (!$this->last_query_key) {
            $this->last_query_key = randid();
        }

        $this->queries[$this->last_query_key] = array(
            'select_arr' => $this->select_arr,
            'from_arr' => $this->from_arr,
            'inner_arr' => $this->inner_arr,
            'left_arr' => $this->left_arr,
            'right_arr' => $this->right_arr,
            'having_arr' => $this->having_arr,
            'group_arr' => $this->group_arr,
            'where_order' => $this->where_order,
            'distinct_var' => $this->distinct_var,
            'order_bys' => $this->order_bys,
            'limit_arr' => $this->limit_arr,
            'main_alias' => $this->main_alias,
            'query' => $this->query,
            'command' => $this->command,
            'into_arr' => $this->into_arr,
            'update_arr' => $this->update_arr,
            'insert_arr' => $this->insert_arr,
            'delete_arr' => $this->delete_arr,
            'set_arr' => $this->set_arr,
            'values_arr' => $this->values_arr,
            'prepared_args' => $this->prepared_args,
        );

        $this->select_arr = array();
        $this->from_arr = '';
        $this->inner_arr = array();
        $this->left_arr = array();
        $this->right_arr = array();
        $this->having_arr = array();
        $this->group_arr = array();
        $this->where_order = array();
        $this->distinct_var = false;
        $this->order_bys = array();
        $this->limit_arr = array();
        $this->main_alias = array();
        $this->query = '';
        $this->command = false;
        $this->into_arr = false;
        $this->update_arr = [];
        $this->insert_arr = [];
        $this->delete_arr = [];
        $this->set_arr = [];
        $this->values_arr = [];
        $this->prepared_args = [];

        if ($this->clear_after) {
            $this->execute_query = false;
        }

        return $this->queries[$this->last_query_key]['query'];

    }

    /**
     * @return bool|mixed
     */
    public function getLastQuery()
    {
        if (array_key_exists($this->last_query_key, $this->queries)) {
            return $this->queries[$this->last_query_key];
        }
        return false;
    }

    /**
     * @param $condition
     * @param $key
     * @param $value
     * @return string
     */
    protected function getStatement($condition, $key, $value, $extra = null)
    {
        if ($condition == DbDict::BETWEEN) {
            if (count($value) == 2) {
                if ($this->isPrepared($key, $value[0])) {
                    $this->isPrepared($key, $value[1]);
                    return $key . ' ' . $condition . ' ? AND ?';
                }
                return $key . ' ' . $condition . ' "' . $value[0] . '" AND "' . $value[1] . '"';
            }
        } else if ($condition == DbDict::NOTIN || $condition == DbDict::IN) {
            $format = '(';
            for ($x = 0; $x < count($value); $x++) {
                if ($x == 0) {
                    $format .= $this->isPrepared($key, $value[$x]) ? '?' : '"' . $value[$x] . '"';
                } else {
                    $format .= $this->isPrepared($key, $value[$x]) ? ', ?' : ', "' . $value[$x] . '"';
                }
            }
            $format .= ')';
            return $key . ' ' . $condition . ' ' . $format;
        } else {
            if ($extra != null) {
                if ($extra == 'SUB_SELECT') {
                    return $this->isPrepared($key, $value) ? '(' . $key . ') ' . $condition . ' ?' : '(' . $key . ') ' . $condition . ' "' . $value . '"';
                } else if ($extra == 'ARGUMENT') {

                    return $this->isPrepared($key, $value) ? $key . ' ' . $condition . ' ?' : $key . ' ' . $condition . ' ' . $value . '';
                }
            } else {
                return $this->isPrepared($key, $value) ? $key . ' ' . $condition . ' ?' : $key . ' ' . $condition . ' "' . $value . '"';
            }
        }
        return null;
    }

    private function addWhereToQuery($query = '')
    {
        $parenthesis = 0;
        foreach ($this->where_order as $key => $array) {
            if ($array['type'] == 'beggin') {
                $parenthesis++;
                $query .= ' WHERE(' . $this->getStatement($array['condition'], $array['key'], $array['value'], $array['extra']) . '';
            } else if ($array['type'] == 'and') {
                $parenthesis++;
                $query .= ' AND (' . $this->getStatement($array['condition'], $array['key'], $array['value'], $array['extra']) . '';
            } else if ($array['type'] == 'or') {
                $next = $this->findNextWhereOrder($key);
                if ($next) {
                    if ($next['type'] == 'or') {
                        $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value'], $array['extra']) . '';
                    } else {
                        if ($parenthesis > 0) {
                            $parenthesis--;
                        }
                        $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value'], $array['extra']) . ')';
                    }
                } else {
                    if ($parenthesis > 0) {
                        $parenthesis--;
                    }
                    $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value'], $array['extra']) . ')';
                }
            }
        }

        for ($x = 0; $x < $parenthesis; $x++) {
            $query .= ')';
        }
        return $query;
    }

    private function addJoinToQuery($query = '')
    {
        foreach ($this->inner_arr as $key => $value) {
            $query .= ' ' . $value;
        }

        foreach ($this->left_arr as $key => $value) {
            $query .= ' ' . $value;
        }

        foreach ($this->right_arr as $key => $value) {
            $query .= ' ' . $value;
        }
        return $query;
    }

    private function addGroupByToQuery($query = '')
    {
        $first = true;
        foreach ($this->group_arr as $key => $value) {
            if ($first) {
                $first = false;
                $query .= ' GROUP BY ' . $value;
            } else {
                $query .= ', ' . $value;
            }
        }
        return $query;
    }

    private function addHavingToQuery($query = '')
    {
        $parenthesis = 0;

        foreach ($this->having_arr as $key => $array) {
            if ($array['type'] == 'beggin') {
                $parenthesis++;
                $query .= ' HAVING(' . $this->getStatement($array['condition'], $array['key'], $array['value']) . '';
            } else if ($array['type'] == 'and') {
                $parenthesis++;
                $query .= ' AND (' . $this->getStatement($array['condition'], $array['key'], $array['value']) . '';
            } else if ($array['type'] == 'or') {
                $next = $this->findNextWhereOrder($key);
                if ($next) {
                    if ($next['type'] == 'or') {
                        $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value']) . '';
                    } else {
                        if ($parenthesis > 0) {
                            $parenthesis--;
                        }
                        $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value']) . ')';
                    }
                } else {
                    if ($parenthesis > 0) {
                        $parenthesis--;
                    }
                    $query .= ' OR ' . $this->getStatement($array['condition'], $array['key'], $array['value']) . ')';
                }
            }
        }

        for ($x = 0; $x < $parenthesis; $x++) {
            $query .= ')';
        }
        return $query;
    }

    private function addOrderByToQuery($query = '')
    {
        $first = true;
        foreach ($this->order_bys as $key => $value) {
            if ($first) {
                $first = false;
                $query .= ' ORDER BY ' . $value[1] . ' ' . $value[0];
            } else {
                $query .= ', ' . $value[1] . ' ' . $value[0];
            }
        }
        return $query;
    }

    private function addLimitToQuery($query = '')
    {
        if (count($this->limit_arr) > 0) {
            if ($this->limit_arr['final'] == 0) {
                if ($this->addPrepared('i', $this->limit_arr['init'])) {
                    $query .= ' LIMIT ?';
                } else {
                    $query .= ' LIMIT ' . $this->limit_arr['init'];
                }
            } else {
                if ($this->addPrepared('i', $this->limit_arr['init'])) {
                    $this->addPrepared('i', $this->limit_arr['final']);
                    $query .= ' LIMIT ?, ?';
                } else {
                    $query .= ' LIMIT ' . $this->limit_arr['init'] . ', ' . $this->limit_arr['final'];
                }

            }
        }
        return $query;
    }

    private function addSetToQuery($query = '')
    {
        if (count($this->set_arr) > 0) {
            $query .= ' SET ';
            $first = true;
            foreach ($this->set_arr as $key => $value) {
                if ($first) {
                    $first = false;
                    $query .= $this->isPrepared($key, $value) ? $key . ' = ?' : $key . ' = "' . $value . '"';
                } else {
                    $query .= $this->isPrepared($key, $value) ? ', ' . $key . ' = ?' : ', ' . $key . ' = "' . $value . '"';
                }
            }
        }
        return $query;
    }

    private function addValuesToQuery($query = '')
    {
        $names = '';
        $values = '';
        if (count($this->values_arr) > 0) {
            $first = true;
            foreach ($this->values_arr as $key => $value) {
                if ($first) {
                    $first = false;
                    $names .= $key;
                    $values .= $this->isPrepared($key, $value) ? '?' : '"' . $value . '"';
                } else {
                    $names .= ', ' . $key;
                    $values .= $this->isPrepared($key, $value) ? ', ?' : ', "' . $value . '"';
                }
            }
            $query .= '(' . $names . ') VALUES(' . $values . ')';
        }
        return $query;
    }

    private function addPrepared($type, $value)
    {
        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                if (!array_key_exists(0, $this->prepared_args)) {
                    $this->prepared_args[0] = '';
                }
                $this->prepared_args[0] .= $type;
            }
            $this->prepared_args[] = $value;
            return true;
        }
        return false;
    }

    private function isPrepared($field, $value)
    {

        if (is_int(strpos($field, '('))) {
            $field = explode('(', $field);
            $field = end($field);
        }

        if (is_int(strpos($field, ')'))) {
            $field = explode(')', $field);
            reset($field);
            $field = current($field);
        }

        $field = explode('.', $field);
        $field = trim(array_pop($field));

        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                if (!array_key_exists_check($field, $this->table_info)) {
                    $type = 's';
                } else {
                    $type = $this->returnMysqlType($this->table_info[$field]);
                }
                if (!array_key_exists(0, $this->prepared_args)) {
                    $this->prepared_args[0] = '';
                }
                $this->prepared_args[0] .= $type;
            }
            $this->prepared_args[] = $value;
            return true;
        }
    }

    protected function getInitialQuery()
    {
        switch ($this->command) {
            case 'select':
                if ($this->distinct_var) {
                    $query = 'SELECT DISTINCT ';
                } else {
                    $query = 'SELECT ';
                }
                if (count($this->from_arr) > 0) {
                    $first = true;
                    foreach ($this->select_arr as $key => $value) {
                        if ($first) {
                            if (is_int($key)) {
                                $query .= $value;
                            } else {
                                $query .= $key . ' AS ' . $value;
                            }
                            $first = false;
                        } else {
                            if (is_int($key)) {
                                $query .= ', ' . $value;
                            } else {
                                $query .= ', ' . $key . ' AS ' . $value;
                            }
                        }
                    }

                    if (count($this->select_arr) == 0) {
                        $query .= "*";
                    }
                    $query .= ' ' . $this->from_arr;
                    return $query;
                }
                return false;
                break;
            case 'update':
                $query = 'UPDATE ';
                $first = true;
                foreach ($this->update_arr as $key => $value) {
                    if (!CurrentDB::tableExists($value)) {
                        CoreError::push(__CLASS__, "Table " . $key . " no exists in database " . WingedConfig::$DBNAME, __FILE__, __LINE__);
                    }
                    if ($first) {
                        if (is_int($key)) {
                            $query .= $value;
                        } else {
                            $query .= $value . ' AS ' . $key;
                        }
                        $first = false;
                    } else {
                        if (is_int($key)) {
                            $query .= ', ' . $value;
                        } else {
                            $query .= ', ' . $value . ' AS ' . $key;
                        }
                    }
                }
                return $query;
                break;
            case 'delete':
                $query = 'DELETE ';
                $first = true;

                foreach ($this->delete_arr as $key => $value) {
                    if (!CurrentDB::tableExists($value)) {
                        CoreError::push(__CLASS__, "Table " . $key . " no exists in database " . WingedConfig::$DBNAME, __FILE__, __LINE__);
                    }
                    if ($first) {
                        $query .= $key;
                    } else {
                        $query .= ', ' . $key;
                    }
                }

                $query .= ' FROM ';

                foreach ($this->delete_arr as $key => $value) {
                    if (!CurrentDB::tableExists($value)) {
                        CoreError::push(__CLASS__, "Table " . $key . " no exists in database " . WingedConfig::$DBNAME, __FILE__, __LINE__);
                    }
                    if ($first) {
                        if (is_int($key)) {
                            $query .= $value;
                        } else {
                            $query .= $value . ' AS ' . $key;
                        }
                        $first = false;
                    } else {
                        if (is_int($key)) {
                            $query .= ', ' . $value;
                        } else {
                            $query .= ', ' . $value . ' AS ' . $key;
                        }
                    }
                }
                return $query;
                break;
            case 'insert':
                $query = 'INSERT INTO ';
                $query .= $this->into_arr . ' ';
                return $query;
                break;
            default:
                return false;
        }
    }

    public function query()
    {
        return $this->last_builded_query;
    }

    public function args()
    {
        return $this->last_builded_args;
    }

    /**
     * @return $this|bool
     */
    public function delegate()
    {
        $this->query = $this->getInitialQuery();
        if ($this->query) {
            switch ($this->command) {
                case 'select':
                    $query = $this->addJoinToQuery($this->query);
                    $query = $this->addWhereToQuery($query);
                    $query = $this->addGroupByToQuery($query);
                    $query = $this->addHavingToQuery($query);
                    $query = $this->addOrderByToQuery($query);
                    $query = $this->addLimitToQuery($query);
                    break;
                case 'update':
                    $query = $this->addJoinToQuery($this->query);
                    $query = $this->addSetToQuery($query);
                    $query = $this->addWhereToQuery($query);
                    break;
                case 'delete':
                    $query = $this->addJoinToQuery($this->query);
                    $query = $this->addWhereToQuery($query);
                    break;
                case 'insert':
                    $query = $this->addValuesToQuery($this->query);
                    break;
                default:
                    return false;
            }
            $this->query = $query;
            $this->saveQuery();
            return $this;
        }
        return false;
    }

    protected function tN($obj)
    {
        $class_name = get_class($obj);
        $reflect = new ReflectionMethod($class_name, 'tableName');
        try {
            return $reflect->invoke(null);
        } catch (Exception $e) {
            return $reflect->invoke($obj);
        }
    }

    protected function fK($obj)
    {
        $class_name = get_class($obj);
        $reflect = new ReflectionMethod($class_name, 'primaryKeyName');
        try {
            return $reflect->invoke(null);
        } catch (Exception $e) {
            return $reflect->invoke($obj);
        }
    }

    protected function fkValue($obj)
    {
        $class_name = get_class($obj);
        $reflect = new ReflectionMethod($class_name, 'primaryKey');
        return $reflect->invoke($obj);
    }

    public function remove()
    {
        $prepared = [];
        if ($this->fkValue($this) != null) {
            if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                    $prepared[] = 's';
                }
                $prepared[] = $this->fkValue($this);
                $query = 'DELETE FROM ' . $this->tN($this) . ' WHERE ' . $this->fK($this) . ' = ?';
                return CurrentDB::execute($query, $prepared);
            } else {
                $query = 'DELETE FROM ' . $this->tN($this) . ' WHERE ' . $this->fK($this) . ' = ' . $this->fkValue($this);
                return CurrentDB::execute($query);
            }
        }
        return false;
    }

    public function createSaveStatement()
    {
        /** @var $this Model */
        $class_name = get_class($this);
        $reflection = new ReflectionClass($class_name);

        $aplly = false;
        if ($this->reverse) {
            $this->reverse = false;
            $this->applyBehaviors();
            $aplly = true;
        }

        $props = get_class_vars($class_name);

        $type = 'insert';

        $update = 0;

        $prepared = [];

        if ($this->primaryKey() != null) {
            $type = 'update';
            $query = 'UPDATE ' . $this->tN($this) . ' SET ';
            $first = true;
            $comma = false;
            foreach ($this->loaded_fields as $key => $field) {
                if (!in_array($field, $this->safe_fields) && $this->{$field} !== null) {
                    if ($field != $this->fK($this)) {
                        if ($this->{$field} !== $this->getOldValue($field)) {
                            $update++;
                            if ($first) {
                                $first = false;
                                if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                                    if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                                        if (!array_key_exists(0, $prepared)) {
                                            $prepared[0] = $this->returnMysqlType($this->table_info[$field]['type']);
                                        } else {
                                            $prepared[0] .= $this->returnMysqlType($this->table_info[$field]['type']);
                                        }
                                    }
                                    $prepared[] = $this->{$field};
                                    $query .= $field . ' = ?';
                                } else {
                                    $query .= $field . ' = "' . $this->{$field} . '"';
                                }
                                $comma = true;
                            } else {
                                if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                                    if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                                        if (!array_key_exists(0, $prepared)) {
                                            $prepared[0] = $this->returnMysqlType($this->table_info[$field]['type']);
                                        } else {
                                            $prepared[0] .= $this->returnMysqlType($this->table_info[$field]['type']);
                                        }
                                    }
                                    $prepared[] = $this->{$field};
                                    $query .= ', ' . $field . ' = ?';
                                } else {
                                    $query .= ', ' . $field . ' = "' . $this->{$field} . '"';
                                }
                            }
                        }
                    }
                }
            }

            $first = true;

            foreach ($props as $field => $value) {
                if (!in_array($field, $this->loaded_fields) && in_array($field, $this->table_fields)) {
                    if ($this->{$field} !== $this->getOldValue($field)) {
                        $update++;
                        if ($this->{$field} !== null) {
                            if ($first && !$comma) {
                                $first = false;
                                $query .= $field . ' = "' . $this->{$field} . '"';
                                $comma = true;
                            } else {
                                $query .= ', ' . $field . ' = "' . $this->{$field} . '"';
                            }
                        }

                    }
                }
            }
            if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                    if (!array_key_exists(0, $prepared)) {
                        $prepared[0] = $this->returnMysqlType($this->table_info[$this->fK($this)]['type']);
                    } else {
                        $prepared[0] .= $this->returnMysqlType($this->table_info[$this->fK($this)]['type']);
                    }
                }
                $prepared[] = $this->{$this->fK($this)};
                $query .= ' WHERE ' . $this->fK($this) . ' = ' . '?';
            } else {
                $query .= ' WHERE ' . $this->fK($this) . ' = ' . '"' . $this->{$this->fK($this)} . '"';
            }
        } else {
            $query = 'INSERT INTO ' . $this->tN($this) . '(';
            $fields = '';
            $values = '';
            $first = true;
            foreach ($this->loaded_fields as $key => $field) {
                if (!in_array($field, $this->safe_fields) && $this->{$field} !== null) {
                    if ($first) {
                        $first = false;
                        $fields .= $field;
                        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                            if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                                if (!array_key_exists(0, $prepared)) {
                                    $prepared[0] = $this->returnMysqlType($this->table_info[$field]['type']);
                                } else {
                                    $prepared[0] .= $this->returnMysqlType($this->table_info[$field]['type']);
                                }
                            }
                            $prepared[] = $this->{$field};
                            $values = '?';
                        } else {
                            $values = '"' . $this->{$field} . '"';
                        }
                    } else {
                        $fields .= ', ' . $field;
                        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                            if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
                                if (!array_key_exists(0, $prepared)) {
                                    $prepared[0] = $this->returnMysqlType($this->table_info[$field]['type']);
                                } else {
                                    $prepared[0] .= $this->returnMysqlType($this->table_info[$field]['type']);
                                }
                            }
                            $prepared[] = $this->{$field};
                            $values .= ', ?';
                        } else {
                            $values .= ', "' . $this->{$field} . '"';
                        }
                    }
                }
            }
            $query .= $fields . ') VALUES(' . $values . ')';
        }
        if ($query) {
            if ($type == 'insert') {
                if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $last = CurrentDB::insert($query, $prepared);
                } else {
                    $last = CurrentDB::insert($query);
                }
                if ($last) {
                    $reflection->getProperty($this->fK($this))->setValue($this, $last);
                }
            } else {
                if ($update > 0) {
                    if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                        $last = CurrentDB::execute($query, $prepared);
                    } else {
                        $last = CurrentDB::execute($query);
                    }
                    if ($last) {
                        $last = $this->primaryKey();
                    }
                    $this->change_in_last_update = true;
                } else {
                    $last = true;
                    $this->change_in_last_update = false;
                }
            }
            if ($aplly) {
                $this->activeReverse();
            }
            return $last;
        }
        return false;
    }

    private function returnMysqlType($str)
    {
        if ($str === null || !is_string($str)) return 's';
        $arr = [
            'varchar' => 's',
            'timestamp' => 's',
            'int' => 'i',
            'tinyint' => 'i',
            'smallint' => 'i',
            'bigint' => 'i',
            'float' => 'd',
            'double' => 'd',
            'longint' => 'i',
            'blob' => 'b',
            'longblob' => 'b',
        ];

        $str = explode('|', str_replace(['(', ' '], '|', $str));
        $str = array_shift($str);

        if (array_key_exists($str, $arr)) {
            return $arr[$str];
        }
        return 's';
    }
}