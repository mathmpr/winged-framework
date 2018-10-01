<?php

namespace Winged\Database;

use Winged\WingedConfig;
use Winged\Winged;
use Winged\Date\Date;
use Winged\Model\Model;

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

    protected function getRealValue($value, $key)
    {
        if (!is_object($value) && in_array($key, ['date', 'time', 'year', 'timestamp', 'datetime'])) {
            switch ($key) {
                case 'date':
                    if (Date::valid($value)) {
                        $value = new Date($value);
                    }
                    break;
                case 'time':
                    if (Date::valid('1994-09-15 ' . $value)) {
                        $value = new Date('1994-09-15 ' . $value);
                    }
                    break;
                case 'year':
                    if (strlen($value) === 4 && intval($value) > 0) {
                        $value = new Date($value . '-09-15');
                    }
                    break;
                case 'timestamp':
                    if (Date::valid($value)) {
                        $value = new Date($value);
                    }
                    break;
                case 'datetime':
                    if (Date::valid($value)) {
                        $value = new Date($value);
                    }
                    break;
                default:
                    break;
            }
        }
        return $value;
    }

    protected function setValueToDb($value, $key)
    {
        if (in_array($key, ['date', 'time', 'year', 'timestamp', 'datetime'])) {
            if (is_object($value)) {
                if (get_class($value) == 'Date') {
                    /**
                     * @var $value Date
                     */
                    switch ($key) {
                        case 'date':
                            return $value->custom('%Y-%m-%d');
                            break;
                        case 'time':
                            return $value->custom('%H:%k:%M');
                            break;
                        case 'year':
                            return $value->custom('%Y');
                            break;
                        case 'timestamp':
                            return $value->sql();
                            break;
                        case 'datetime':
                            return $value->sql();
                            break;
                        default:
                            return $value;
                    }
                }
            }
        }
        return $value;
    }

    private function realValueOf($array)
    {
        if (is_array($array)) {
            if (array_key_exists(0, $array)) {
                foreach ($array as $key => $uni) {
                    foreach ($uni as $_key => $u) {
                        $type = $this->returnMysqlType($_key);
                        if ($type['type'] == 'i') {
                            $array[$key][$_key] = (int)($u);
                            $this->{$_key} = $array[$key][$_key];
                        } else if ($type['type'] == 'd') {
                            $array[$key][$_key] = (float)($u);
                            $this->{$_key} = $array[$key][$_key];
                        } else {
                            if (WingedConfig::$USE_PREPARED_STMT !== USE_PREPARED_STMT) {
                                $array[$key][$_key] = stripslashes((string)$u);
                            }
                            $array[$key][$_key] = $this->getRealValue($array[$key][$_key], $type['key']);
                            $this->{$_key} = $array[$key][$_key];
                        }

                        $reverse = [];

                        if (method_exists($this, 'reverseBehaviors')) {
                            $reverse = $this->reverseBehaviors();
                        }

                        if (array_key_exists($_key, $reverse)) {
                            $this->{$_key} = $array[$key][$_key];
                            $apply = $reverse[$_key];
                            if (is_callable($apply)) {
                                $apply = call_user_func($apply);
                                if (is_array($apply)) {
                                    if (array_key_exists('null', $apply) && $apply['null'] === null) {
                                        $apply = null;
                                    }
                                    $array[$key][$_key] = $apply;
                                }
                                if ($apply !== null) {
                                    if ($apply !== $array[$key][$_key]) {
                                        $array[$key][$_key] = $apply;
                                    }
                                }
                            } else {
                                $array[$key][$_key] = $apply;
                            }
                        }
                    }
                }
            } else {
                foreach ($array as $_key => $u) {
                    $type = $this->returnMysqlType($_key);
                    if ($type['type'] === 'i') {
                        $array[$_key] = (int)($u);
                        $this->{$_key} = $array[$_key];
                    } else if ($type['type'] === 'd') {
                        $array[$_key] = (float)($u);
                        $this->{$_key} = $array[$_key];
                    } else {
                        if (WingedConfig::$USE_PREPARED_STMT !== USE_PREPARED_STMT) {
                            $array[$_key] = stripslashes((string)$u);
                        }
                        $array[$_key] = $this->getRealValue($array[$_key], $type['key']);
                        $this->{$_key} = $array[$_key];
                    }

                    $reverse = [];

                    if (method_exists($this, 'reverseBehaviors')) {
                        $reverse = $this->reverseBehaviors();
                    }

                    if (array_key_exists($_key, $reverse)) {
                        $this->{$_key} = $array[$_key];
                        $apply = $reverse[$_key];
                        if (is_callable($apply)) {
                            $apply = call_user_func($apply);
                            if (is_array($apply)) {
                                if (array_key_exists('null', $apply) && $apply['null'] === null) {
                                    $apply = null;
                                }
                                $array[$_key] = $apply;
                            }
                            if ($apply !== null) {
                                if ($apply !== $array[$_key]) {
                                    $array[$_key] = $apply;
                                }
                            }
                        } else {
                            $array[$_key] = $apply;
                        }
                    }
                }
            }
        }
        return $array;
    }

    private function realInsert()
    {
        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            $args = $this->delegate()->getQueryInfo();
            return CurrentDB::insert($args['query'], $args['args']);
        }else{
            return CurrentDB::insert($this->delegate()->query());
        }
    }

    private function realExecute()
    {
        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            $args = $this->delegate()->getQueryInfo();
            return CurrentDB::execute($args['query'], $args['args']);
        }else{
            return CurrentDB::execute($this->delegate()->query());
        }
    }

    private function fetch()
    {
        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            $args = $this->delegate()->getQueryInfo();
            return CurrentDB::fetch($args['query'], $args['args']);
        } else {
            return CurrentDB::fetch($this->delegate()->query());
        }
    }

    public function find($as_array = false)
    {
        $unic = $this->fetch();
        if (count7($unic) > 0 && is_array($unic)) {
            if ($as_array) {
                return $this->realValueOf($unic);
            }
            $models = array();
            $unic = $this->realValueOf($unic);
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
        if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
            $query = $cloned->delegate()->getQueryInfo();
            $count = CurrentDB::count($query['query'], $query['args']);
        } else {
            $query = $cloned->delegate()->query();
            $count = CurrentDB::count($query);
        }
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
        if (count7($unic) > 0 && is_array($unic)) {
            $unic = $unic[0];
            if ($as_array) {
                foreach ($unic as $key => $uni) {
                    $unic[$key] = stripslashes($uni);
                }
                return $this->realValueOf($unic);
            }
            return $this->populateModel($this->getNewObjectType(), $this->realValueOf($unic));
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

        $unic = $obj->fetch();

        if ($as_array) {
            if (count7($unic) > 0 && is_array($unic)) {
                return $this->realValueOf($unic);
            }
            return null;
        }

        if (count7($unic) > 0 && is_array($unic)) {
            $unic = $this->realValueOf($unic);
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
            if (count7($unic) > 0 && is_array($unic)) {
                $unic = $unic[0];
                return $this->realValueOf($unic);
            }
            return null;
        }
        $unic = $obj->fetch();
        if (count7($unic) > 0 && is_array($unic)) {
            $unic = $this->realValueOf($unic[0]);
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
            if (count7($preg) == 2) {
                $key = trim(array_pop($preg));
            } else {
                $exp = explode('.', $key);
                if (count7($exp) == 2) {
                    $key = trim(array_pop($exp));
                }
            }
        }
        $ret = [];
        if ($arr && !empty($arr) && count7($arr) > 0) {
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
        $unic = $obj->fetch();
        if ($as_array) {
            if (count7($unic) > 0 && is_array($unic)) {
                return $this->realValueOf($unic);
            }
            return null;
        }
        if (count7($unic) > 0 && is_array($unic)) {
            $models = array();
            $unic = $this->realValueOf($unic);
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
            if (count7($unic) > 0 && is_array($unic)) {
                return $this->realValueOf($unic[0]);
            }
            return null;
        }
        $unic = $obj->fetch();
        if (count7($unic) > 0 && is_array($unic)) {
            $unic = $this->realValueOf($unic[0]);
            return $this->populateModel($obj, $unic);
        }
        return null;
    }

    protected function populateModel(Model $obj, $array)
    {
        $class_name = get_class($obj);
        foreach ($array as $key => $value) {
            $lkey = strtolower($key);
            $type = $this->returnMysqlType($key);
            if (property_exists($class_name, $lkey)) {
                //$obj->{$lkey} = $this->getRealValue($value, $type['key']);
                //$obj->old_values[$lkey] = $this->getRealValue($value, $type['key']);
                $obj->{$lkey} = $value;
                $obj->old_values[$lkey] = $value;
            } else {
                //$obj->extras->{$lkey} = $this->getRealValue($value, $type['key']);
                $obj->extras->{$lkey} = $value;
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
     * @return array
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

    private function destroyQuery()
    {
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

        $this->destroyQuery();

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
        if ($condition == DbDict::IS_NOT_NULL) {
            return $value . ' IS NOT NULL';
        } else if ($condition == DbDict::IS_NULL) {
            return $value . ' IS NULL';
        } else if ($condition == DbDict::BETWEEN) {
            if (count7($value) == 2) {
                $this->prepare($key, $value[0]);
                $this->prepare($key, $value[1]);
                return $key . ' ' . $condition . ' ? AND ?';
            }
        } else if ($condition == DbDict::NOTIN || $condition == DbDict::IN) {
            $names = [];
            for ($x = 0; $x < count7($value); $x++) {
                $names[] = '?';
                $this->prepare($key, $value[$x]);
            }
            return $key . ' ' . $condition . ' ' . '(' . join(', ', $names) . ')';
        } else {
            if ($extra != null) {
                if ($extra == 'SUB_SELECT') {
                    $this->prepare($key, $value);
                    return '(' . $key . ') ' . $condition . ' ?';
                } else if ($extra == 'ARGUMENT') {
                    $this->prepare($key, $value);
                    return $key . ' ' . $condition . ' ?';
                }
            } else {
                $this->prepare($key, $value);
                return $key . ' ' . $condition . ' ?';
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
        if (count7($this->limit_arr) > 0) {
            if ($this->limit_arr['final'] == 0) {
                $this->addPrepared('i', $this->limit_arr['init']);
                $query .= ' LIMIT ?';
            } else {
                $this->addPrepared('i', $this->limit_arr['init']);
                $this->addPrepared('i', $this->limit_arr['final']);
                $query .= ' LIMIT ?, ?';
            }
        }
        return $query;
    }

    private function addSetToQuery($query = '')
    {
        if (count7($this->set_arr) > 0) {
            $query .= ' SET ';
            $names = [];
            foreach ($this->set_arr as $key => $value) {
                $names[] = $key . ' = ?';
                $this->prepare($key, $value);
            }
            $query .= join(', ', $names);
        }
        return $query;
    }

    private function addValuesToQuery($query = '')
    {
        $names = [];
        $values = [];
        if (count7($this->values_arr) > 0) {
            foreach ($this->values_arr as $key => $value) {
                $this->prepare($key, $value);
                $names[] = $key;
                $values[] = '?';
            }
            $query .= '(' . join(', ', $names) . ') VALUES(' . join(', ', $values) . ')';
        }
        return $query;
    }

    private function addPrepared($type, $value)
    {
        if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
            if (!array_key_exists(0, $this->prepared_args)) {
                $this->prepared_args[0] = '';
            }
            $this->prepared_args[0] .= $type;
        }
        $this->prepared_args[$type . '-' . randid(6)] = $value;
        return true;
    }

    private function prepare($field, $value)
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

        if (!is_object($value) && WingedConfig::$USE_PREPARED_STMT !== USE_PREPARED_STMT) {
            $value = rtrim(addslashes($value), '/');
        }

        if (WingedConfig::$STD_DB_CLASS === IS_MYSQLI) {
            if (!array_key_exists_check($field, $this->table_info)) {
                $type = ['type' => 's', 'key' => 's_s'];
            } else {
                $type = $this->returnMysqlType($field);
            }
            if (!array_key_exists(0, $this->prepared_args)) {
                $this->prepared_args[0] = '';
            }
            $this->prepared_args[0] .= $type['type'];
            $value = $this->setValueToDb($value, $type['key']);
        }
        $cp_field = $field;
        while (array_key_exists($field, $this->prepared_args)) {
            $field = $cp_field . '-' . randid(6);
        }
        $this->prepared_args[$field] = $value;
        return true;
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
                $selectArr = is_array($this->select_arr) ? count7($this->select_arr) : -1;
                if ($selectArr > 0) {
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
                }

                if (count7($this->select_arr) == 0) {
                    $query .= "*";
                }
                $query .= ' ' . $this->from_arr;
                return $query;

                break;
            case 'update':
                $query = 'UPDATE ';
                $first = true;
                foreach ($this->update_arr as $key => $value) {
                    if (!CurrentDB::tableExists($value)) {
                        Winged::fatalError(__CLASS__, "Table " . $key . " no exists in database " . WingedConfig::$DBNAME, true);
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
                $query = 'DELETE %replace_alias% FROM ';
                $alias = '';
                $first = true;
                foreach ($this->delete_arr as $key => $value) {
                    if (!CurrentDB::tableExists($value)) {
                        Winged::fatalError(__CLASS__, "Table " . $key . " no exists in database " . WingedConfig::$DBNAME, true);
                    }
                    if ($first) {
                        if (is_int($key)) {
                            $query .= $value;
                        } else {
                            $query .= $value . ' AS ' . $key;
                            $alias .= $key;
                        }
                        $first = false;
                    } else {
                        if (is_int($key)) {
                            $query .= ', ' . $value;
                        } else {
                            $query .= ', ' . $value . ' AS ' . $key;
                            $alias .= ', ' . $key;
                        }
                    }
                }

                if ($alias != '') {
                    $query = str_replace('%replace_alias%', $alias, $query);
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
        $args = $this->getQueryInfo();
        $query = $args['query'];
        if (!empty($args['args'])) {
            $values = $args['args'];
            array_shift($values);
            $query = str_replace('?', '%s', $query);
            foreach ($values as $key => $value) {
                $values[$key] = $this->typeOfValue($key, $value);
            }
            $values = array_merge([$query], $values);
            $query = call_user_func_array('sprintf', $values);
        }
        return $query;
    }

    public function args()
    {
        return $this->last_builded_args;
    }

    /**
     * @return $this | bool
     */
    public function delegate()
    {
        $this->prepared_args = array_values($this->prepared_args);
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
    }

    protected function tN($obj)
    {
        $class_name = get_class($obj);
        $reflect = new \ReflectionMethod($class_name, 'tableName');
        try {
            return $reflect->invoke(null);
        } catch (\Exception $e) {
            return $reflect->invoke($obj);
        }
    }

    protected function fK($obj)
    {
        $class_name = get_class($obj);
        $reflect = new \ReflectionMethod($class_name, 'primaryKeyName');
        try {
            return $reflect->invoke(null);
        } catch (\Exception $e) {
            return $reflect->invoke($obj);
        }
    }

    protected function fkValue($obj)
    {
        $class_name = get_class($obj);
        $reflect = new \ReflectionMethod($class_name, 'primaryKey');
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
        $reflection = new \ReflectionClass($class_name);
        $props = get_class_vars($class_name);
        if ($this->primaryKey() != null) {
            $alias = randid(6);
            $this->update([$alias => $this->tN($this)])
                ->where(DbDict::EQUAL, [$alias . '.' . $this->fK($this) => $this->primaryKey()]);
            $real_load = [];
            foreach ($this->loaded_fields as $key => $field) {
                if (!in_array($field, $this->safe_fields) && $this->{$field} !== null) {
                    if ($field != $this->fK($this)) {
                        if ($this->{$field} !== $this->getOldValue($field)) {
                            if(is_object($this->{$field})){
                                if(get_class($this->{$field}) === 'Winged\Date\Date'){
                                    $real_load[$field] = $this->{$field}->sql();
                                }
                            }else{
                                $real_load[$field] = $this->{$field};
                            }
                        }
                    }
                }
            }
            foreach ($props as $field => $value) {
                if (!in_array($field, $this->loaded_fields) && in_array($field, $this->table_fields)) {
                    if ($this->{$field} !== $this->getOldValue($field)) {
                        if ($this->{$field} !== null) {
                            if(is_object($this->{$field})){
                                if(get_class($this->{$field}) === 'Winged\Date\Date'){
                                    $real_load[$field] = $this->{$field}->sql();
                                }
                            }else{
                                $real_load[$field] = $this->{$field};
                            }
                        }
                    }
                }
            }
            $this->set($real_load);
            $prepared = false;
            if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                $query = $this->delegate()->getQueryInfo();
                $prepared = $query['args'];
                $query = $query['query'];
            } else {
                $query = $this->delegate()->query();
            }
            if (!empty($real_load) > 0) {
                if ($prepared) {
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
        } else {
            $this->insert()->into($this->tN($this));
            $real_load = [];
            foreach ($this->loaded_fields as $key => $field) {
                if (!in_array($field, $this->safe_fields) && $this->{$field} !== null) {
                    if(is_object($this->{$field})){
                        if(get_class($this->{$field}) === 'Winged\Date\Date'){
                            $real_load[$field] = $this->{$field}->sql();
                        }
                    }else{
                        $real_load[$field] = $this->{$field};
                    }
                }
            }
            $this->values($real_load);
            $prepared = false;
            if (WingedConfig::$USE_PREPARED_STMT === USE_PREPARED_STMT) {
                $query = $this->delegate()->getQueryInfo();
                $prepared = $query['args'];
                $query = $query['query'];
            } else {
                $query = $this->delegate()->query();
            }
            if ($prepared) {
                $last = CurrentDB::insert($query, $prepared);
            } else {
                $last = CurrentDB::insert($query);
            }
            if ($last) {
                $reflection->getProperty($this->fK($this))->setValue($this, $last);
            }
        }
        $this->_reverse();
        $this->unloadAll();
        return $last;
    }

    private function typeOfValue($key, $value)
    {
        if (numeric_is($value) || ($this->returnMysqlType($key)['type'] == 'i' || $this->returnMysqlType($key)['type'] == 'd')) {
            return $value;
        } else {
            return '"' . $value . '"';
        }
    }

    protected function returnMysqlType($field)
    {
        $arr = [
            'tinyint' => 'i',
            'smallint' => 'i',
            'mediumint' => 'i',
            'int' => 'i',
            'integer' => 'i',
            'bigint' => 'i',
            'bit' => 'i',
            'real' => 'd',
            'double' => 'd',
            'float' => 'd',
            'decimal' => 'd',
            'numeric' => 'd',
            'char' => 's',
            'varchar' => 's',
            'date' => 's',
            'time' => 's',
            'year' => 's',
            'timestamp' => 's',
            'datetime' => 's',
            'tinyblob' => 'b',
            'blob' => 'b',
            'mediumblob' => 'b',
            'longblob' => 'b',
            'tinytext' => 's',
            'text' => 's',
            'mediumtext' => 's',
            'longtext' => 's',
            'enum' => 's',
            'set' => 's',
            'binary' => 'b',
            'varbinary' => 'b',
            'point' => 's',
            'linestring' => 's',
            'polygon' => 's',
            'geometry' => 's',
            'multipoint' => 's',
            'multilinestring' => 's',
            'multipolygon' => 's',
            'geometrycollection' => 's',
            'json' => 's'
        ];
        if ($field === null) return ['key' => false, 'type' => 's'];
        if (array_key_exists($field, $this->table_info)) {
            $field = $this->table_info[$field]['type'];
            $type = explode('(', $field);
            $type = trim(array_shift($type));
            foreach ($arr as $key => $value) {
                if ($key === $type) {
                    return ['key' => $key, 'type' => $value];
                }
            }
            return ['key' => false, 'type' => 's'];
        } else {
            $exp = explode('-', $field);
            $exp = array_shift($exp);
            if (strlen($exp) === 1) {
                if (in_array($exp, ['s', 'i', 'd', 'b']) && !in_array($field, $this->table_fields)) {
                    return ['key' => false, 'type' => $exp];
                } else {
                    $exp = explode('-', $field);
                    $exp = array_shift($exp);
                    if (in_array($exp, $this->table_fields)) {
                        $field = $this->table_info[$field]['type'];
                        $type = explode('(', $field);
                        $type = trim(array_shift($type));
                        foreach ($arr as $key => $value) {
                            if ($key === $type) {
                                return ['key' => $key, 'type' => $value];
                            }
                        }
                        return ['key' => false, 'type' => 's'];
                    }
                }
            }
            return ['key' => false, 'type' => 's'];
        }
    }
}