<?php

namespace Winged\Model;

use Winged\Database\DbDict;
use Winged\Database\CurrentDB;
use Winged\Database\AbstractEloquent;
use WingedConfig;

/**
 * Class Model
 *
 * @package Winged\Model
 */
abstract class Model extends AbstractEloquent
{
    public $errors = [];

    public $extras = false;

    /**
     * register callback to execute when save method as called and return error
     *
     * @var array $onSaveError
     */
    private $onSaveError = [];

    /**
     * register callback to execute when save method as called and return success
     *
     * @var array $onSaveSuccess
     */
    private $onSaveSuccess = [];

    /**
     * register callback to execute when validate method as called and return success
     *
     * @var array $onValidateSuccess
     */
    private $onValidateSuccess = [];

    /**
     * register callback to execute when validate method as called and return error
     *
     * @var array $onValidateError
     */
    private $onValidateError = [];

    /**
     * register name of properties as parsed in last load call
     *
     * @var null | \stdClass $parsedProperties
     */
    private $parsedProperties = null;

    /**
     * register name of properties as reversed in last select
     *
     * @var null | \stdClass $reversedProperties
     */
    private $reversedProperties = null;

    /**
     * contains all keys / properties loaded in last load call
     *
     * @var array $loadedFields
     */
    private $loadedFields = [];

    /**
     * contain all fields names of a table
     *
     * @var array $tableFields
     */
    private $tableFields = [];

    /**
     * contain infos of a table
     *
     * @var array $tableInfo
     */
    private $tableInfo = [];

    /**
     * after any call of load, behavior or reverseBehaviors the old value is stored in this backup
     *
     * @var null | \stdClass
     */
    private $backup = null;

    /**
     * store a cache information of a table
     * with this, a new Model() do not need make a new query on database
     *
     * @var array
     */
    protected static $cached_info = [];

    /**
     * @param bool $pk
     *
     * @return int | $this
     */
    abstract public function primaryKey($pk = false);

    /**
     * @return array
     */
    abstract public function behaviors();

    /**
     * @return array
     */
    abstract public function reverseBehaviors();

    /**
     * @return array
     */
    abstract public function rules();

    /**
     * @return array
     */
    abstract public function messages();

    /**
     * @return array
     */
    abstract public function labels();

    /**
     * Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        try {
            $this->tableFields();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }
        if (!$this->extras) {
            $this->extras = new \stdClass();
        }
        $this->backup = new \stdClass();
        $this->parsedProperties = new \stdClass();
        $this->reversedProperties = new \stdClass();
    }

    /**
     * get table fields and fields informations
     *
     * @return mixed
     * @throws \Exception
     *
     */
    public function tableFields()
    {
        $class_name = get_class($this);
        if (array_key_exists($class_name, self::$cached_info)) {
            $this->tableFields = self::$cached_info[$class_name]->table_fields;
            $this->tableInfo = self::$cached_info[$class_name]->table_info;
            return self::$cached_info[$class_name]->table_fields;
        } else {
            if (WingedConfig::$config->db()->VALIDATE_MODELS) {
                if (!array_key_exists($this->_tableName(), $this->eloquent->database->db_tables)) {
                    throw new \Exception('table ' . $this->_tableName() . ' no exists in database ' . $this->eloquent->database->dbname);
                }
            }
            if (WingedConfig::$config->db()->VALIDATE_MODELS) {
                foreach ($this->eloquent->database->db_tables[$this->_tableName()]['fields'] as $key => $value) {
                    if (!property_exists($this, $key)) {
                        throw new \Exception('property ' . $key . ' no exists in Model ' . get_class($this));
                    }
                }
            }
            if (empty($this->table_fields) && $this->_tableName() != '' && array_key_exists($this->_tableName(), CurrentDB::$current->db_tables)) {
                $all = CurrentDB::$current->db_tables[$this->_tableName()]['fields'];
                foreach ($all as $key => $field) {
                    $this->tableFields[] = $key;
                    $this->tableInfo[$key] = $field;
                }
            }
            if (!array_key_exists($class_name, self::$cached_info)) {
                self::$cached_info[$class_name] = new \stdClass();
                self::$cached_info[$class_name]->table_fields = $this->tableFields;
                self::$cached_info[$class_name]->table_info = $this->tableInfo;
            }
            return $this->tableFields;
        }
    }

    /**
     * Call static method from model to get table name
     *
     * @return string
     */
    protected function _tableName()
    {
        try {
            $reflect = new \ReflectionMethod(get_class($this), 'tableName');
            return $reflect->invoke(null);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Call static method from model to get primary key name
     *
     * @return string
     */
    protected function _primaryKeyName()
    {
        try {
            $reflect = new \ReflectionMethod(get_class($this), 'primaryKeyName');
            return $reflect->invoke(null);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * returns other model with other names based on a one-to-one relationship
     *
     * @param      $className
     * @param bool $leftSidePkName
     * @param bool $rightSidePkName
     *
     * @return array | null | Model
     */
    public function hasOne($className, $leftSidePkName = false, $rightSidePkName = false)
    {
        $results = $this->hasMany($className, $leftSidePkName, $rightSidePkName);
        if ($results) {
            return $results[0];
        }
        return null;
    }

    /**
     * returns other models with other names based on a one-to-one relationship or N for N
     *
     * @param      $className
     * @param bool $leftSidePkName
     * @param bool $rightSidePkName
     *
     * @return null | array | Model[] | Model
     */
    public function hasMany($className, $leftSidePkName = false, $rightSidePkName = false)
    {
        if (is_string($className) && is_string($leftSidePkName) && is_string($rightSidePkName)) {
            if (property_exists($this, $leftSidePkName)) {
                if ((class_exists($className)) ||
                    (class_exists('\Winged\Model\\' . $className))
                ) {
                    $class = class_exists($className) ? $className : '\Winged\Model\\' . $className;
                    try {
                        $reflect = new \ReflectionClass($class);
                    } catch (\Exception $exception) {
                        $reflect = false;
                    }
                    if ($reflect) {
                        $newObject = $reflect->newInstance();
                        if (property_exists($rightSidePkName, $newObject)) {
                            /**
                             * @var $newObject Model
                             */
                            $newObject = $newObject->select()
                                ->from(['LINK' => $class::tableName()])
                                ->where(DbDict::EQUAL, ['LINK.' . $leftSidePkName => $this->{$leftSidePkName}])
                                ->execute();

                            return $newObject;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * get value inside backup if key exists in backup
     *
     * @param $property
     *
     * @return bool
     */
    public function backup($property)
    {
        if (property_exists($property, $this->backup)) {
            return $this->backup->{$property};
        }
        return false;
    }

    /**
     * return extras. extras contains values fetched in select statement
     *
     * @return bool|\stdClass
     */
    public function extra()
    {
        return $this->extras;
    }

    /**
     * unload field in model
     *
     * @param $field
     *
     * @return bool
     */
    public function unload($field)
    {
        if (array_key_exists($field, $this->loadedFields)) {
            unset($this->loadedFields[$field]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * unload all field in model
     *
     * @return $this
     */
    public function unloadAll()
    {
        $this->loadedFields = [];
        return $this;
    }

    /**
     * check if field was loaded
     *
     * @param $field
     *
     * @return bool
     */
    public function loaded($field)
    {
        if (array_key_exists($field, $this->loadedFields)) {
            return true;
        }
        return false;
    }

    /**
     * load values inside properties on model
     *
     * @param array $args
     * @param bool  $withoutBehaviors
     *
     * @return $this
     */
    public function load($args = [], $withoutBehaviors = false)
    {
        $fields_loaded_in_this_call = [];
        $class_name = get_class($this);

        $prepared_data_to_load = [];
        foreach ($args as $key => $value) {
            if (is_array($value) && is_int(stripos($class_name, ucfirst($key)))) {
                if (array_key_exists(0, $value)) {
                    $prepared_data_to_load = $value[0];
                } else {
                    $prepared_data_to_load = $value;
                }
            }
        }

        foreach ($prepared_data_to_load as $key => $value) {
            $value_of_property_before_parse = $this->{$key};
            if (property_exists($class_name, $key)) {
                $this->{$key} = $value;
                $this->loadedFields[$key] = $key;
                $fields_loaded_in_this_call[$key] = $key;
                if ($value_of_property_before_parse !== $this->{$key}) {
                    $this->backup->{$key} = $value_of_property_before_parse;
                }
            }
        }

        if (!$withoutBehaviors) {
            $this->_behaviors();
        }
        return $this;
    }

    /**
     * like load, but for multiple models
     *
     * @param array $args
     * @param bool  $withoutBehaviors
     *
     * @return array
     */
    public function loadMultiple($args = [], $withoutBehaviors = false)
    {
        $class_name = get_class($this);
        $models = [];
        $prepared_data_to_load = [];
        foreach ($args as $key => $value) {
            if (is_array($value) && ucfirst($key) == $class_name) {
                if (array_key_exists(0, $value)) {
                    $prepared_data_to_load = $value;
                } else {
                    $prepared_data_to_load = [$value];
                }
            }
        }

        if (is_array($prepared_data_to_load)) {
            if (count7($prepared_data_to_load) === 1) {
                $prepared_data_to_load = $prepared_data_to_load[0];
                $count = 0;
                foreach ($prepared_data_to_load as $key => $value) {
                    if (count7($value) > $count) {
                        $count = count7($value);
                    }
                }
                for ($x = 0; $x < $count; $x++) {
                    $arr = [
                        $class_name => []
                    ];
                    foreach ($prepared_data_to_load as $key => $value) {
                        if (array_key_exists($x, $prepared_data_to_load[$key])) {
                            $arr[$class_name][$key] = $prepared_data_to_load[$key][$x];
                        }
                    }
                    /**
                     * @var $model Model
                     */
                    $model = new $class_name();
                    $model->load($arr, $withoutBehaviors);
                    $models[] = $model;
                }
            }
        }
        return $models;
    }

    /**
     * run into table fields and
     *
     * @return $this
     */
    public function createBackup()
    {
        foreach ($this->tableFields as $key) {
            if ($this->{$key} != null) {
                $this->backup->{$key} = $this->{$key};
            }
        }
        return $this;
    }

    /**
     * fetch a entire row from database with param $id
     *
     * @param int $id
     *
     * @return bool
     */
    public function autoLoadDb($id = 0)
    {
        $one = $this->findOne($id);
        if ($one) {
            foreach ($this->tableFields as $key) {
                $this->{$key} = $one->{$key};
            }
            $this->createBackup();
        }
        return false;
    }

    /**
     * push a validade error message inside model
     *
     * @param $key
     * @param $error
     * @param $pn
     *
     * @return bool
     */
    protected function pushValidateError($key, $error, $pn)
    {
        if (array_key_exists($key, $this->errors)) {
            $this->errors[$key][$pn] = $error;
        } else {
            $this->errors[$key] = [$pn => $error];
        }
        return true;
    }

    /**
     * validate values in model
     *
     * @return bool
     */
    public function validate()
    {
        $class_name = get_class($this);
        $continue = true;
        if (method_exists($this, 'rules')) {
            $rules = $this->rules();

            $messages = [];

            if (method_exists($this, 'messages')) {
                $messages = $this->messages();
            }

            if (!is_array($messages)) {
                $messages = [];
            }

            if (is_array($rules)) {
                foreach ($rules as $property_rule => $rule) {
                    if (property_exists($class_name, $property_rule)) {
                        $erros = [
                            'required' =>
                                ['text' => 'Field ' . $property_rule . ' is required.', 'filter' => function ($arg) {
                                    if ($arg === null) {
                                        return false;
                                    }
                                    if ($arg === '') {
                                        return null;
                                    }
                                    if (numeric_is($arg) === 0) {
                                        return false;
                                    }
                                    if ($arg === false) {
                                        return false;
                                    }
                                    return true;
                                }],
                            'email' =>
                                ['text' => 'Field ' . $property_rule . ' required a valid email.', 'filter' => FILTER_VALIDATE_EMAIL],
                            'float' =>
                                ['text' => 'Field ' . $property_rule . ' required a valid real number.', 'filter' => FILTER_VALIDATE_FLOAT],
                            'int' =>
                                ['text' => 'Field ' . $property_rule . ' required a valid integer number.', 'filter' => FILTER_VALIDATE_INT],
                            'ip' =>
                                ['text' => 'Field ' . $property_rule . ' required a ip address.', 'filter' => FILTER_VALIDATE_IP],
                            'url' =>
                                ['text' => 'Field ' . $property_rule . ' required a valid url.', 'filter' => FILTER_VALIDATE_URL],
                            'bool' =>
                                ['text' => 'Field ' . $property_rule . ' required a valid boolean value.', 'filter' => FILTER_VALIDATE_BOOLEAN],
                        ];
                        if (is_array($rule)) {
                            $finds = ['required', 'email', 'float', 'int', 'ip', 'url', 'bool'];
                            foreach ($finds as $key => $value) {
                                $finds[$value] = array_key_exists_check($value, $rule);
                                unset($finds[$key]);
                            }
                            array_values($finds);
                            foreach ($finds as $pn => $test) {
                                if (is_callable($test)) {
                                    $test = call_user_func($test);
                                    if ($test) {
                                        $message = $erros[$pn]['text'];
                                        if (array_key_exists($property_rule, $messages)) {
                                            if (array_key_exists($pn, $messages[$property_rule])) {
                                                $message = $messages[$property_rule][$pn];
                                            }
                                        }
                                        $this->pushValidateError($property_rule, $message, $pn);
                                    }
                                } else {
                                    if ($test) {
                                        $message = $erros[$pn]['text'];
                                        if (array_key_exists($property_rule, $messages)) {
                                            if (array_key_exists($pn, $messages[$property_rule])) {
                                                $message = $messages[$property_rule][$pn];
                                            }
                                        }
                                        if ($erros[$pn]['filter']) {
                                            if (is_callable($erros[$pn]['filter'])) {
                                                if (!$erros[$pn]['filter']($this->{$property_rule})) {
                                                    $continue = false;
                                                    $this->pushValidateError($property_rule, $message, $pn);
                                                }
                                            } else {
                                                if (!filter_var($this->{$property_rule}, $erros[$pn]['filter'])) {
                                                    $continue = false;
                                                    $this->pushValidateError($property_rule, $message, $pn);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            foreach ($rule as $pn => $func) {
                                if (!array_key_exists($pn, $erros) && $func !== 'safe') {
                                    $ret = null;
                                    if (is_callable($func)) {
                                        $ret = call_user_func_array($func, [$this->{$property_rule}]);
                                    } else if (is_array($func) && count7($func) >= 2) {
                                        if (is_object($func[0]) && is_string($func[1])) {
                                            if (method_exists($func[0], $func[1])) {
                                                $params = [$this->{$property_rule}];
                                                if (array_key_exists(2, $func)) {
                                                    if (is_array($func[2])) {
                                                        $params = array_merge($params, $func[2]);
                                                    } else {
                                                        $params = array_merge($params, [$func[2]]);
                                                    }
                                                }
                                                $ret = false;
                                                try {
                                                    $reflect = new \ReflectionMethod(get_class($func[0]), $func[1]);
                                                } catch (\Exception $exception) {
                                                    $reflect = false;
                                                }
                                                if ($reflect) {
                                                    $ret = $reflect->invokeArgs($func[0], $params);
                                                }
                                            } else {
                                                trigger_error("Object '" . $func[0] . "' no have method '" . $func[1] . "'", E_USER_ERROR);
                                            }
                                        }
                                        if (is_callable($func[0])) {
                                            $params = [$this->{$property_rule}];
                                            if (array_key_exists(1, $func)) {
                                                if (is_array($func[1])) {
                                                    $params = array_merge($params, $func[1]);
                                                } else {
                                                    $params = array_merge($params, [$func[1]]);
                                                }
                                            }
                                            $ret = call_user_func_array($func[0], $params);
                                        }
                                    } else {
                                        $ret = $func;
                                    }
                                    if ($ret === false) {
                                        $continue = false;
                                        $message = 'Field ' . $property_rule . ' have one or more errors';
                                        if (array_key_exists($property_rule, $messages)) {
                                            if (array_key_exists($pn, $messages[$property_rule])) {
                                                $message = $messages[$property_rule][$pn];
                                            }
                                        }
                                        $this->pushValidateError($property_rule, $message, $pn);
                                    }
                                }
                            }
                        } else {
                            $message = $erros[$rule]['text'];
                            if (array_key_exists($property_rule, $messages)) {
                                if (array_key_exists($rule, $messages[$property_rule])) {
                                    $message = $messages[$property_rule][$rule];
                                }
                            }
                            if ($rule == 'required' && ($this->{$property_rule} === null && $this->{$property_rule} === false && $this->{$property_rule} === '')) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'email' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_EMAIL)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'float' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_FLOAT)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'int' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_INT)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'ip' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_IP)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'url' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_URL)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                            if ($rule == 'bool' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_BOOLEAN)) {
                                $this->pushValidateError($property_rule, $message, $rule);
                                $continue = false;
                            }
                        }
                    }
                }
            }
        }

        if ($continue) {
            foreach ($this->onValidateSuccess as $index => $arr) {
                call_user_func_array($this->onValidateSuccess[$index]['function'], $arr['args']);
            }
        } else {
            $this->_reverse();
            foreach ($this->onValidateError as $index => $arr) {
                call_user_func_array($this->onValidateError[$index]['function'], $arr['args']);
            }
        }

        return $continue;
    }


    /**
     * auto implements insert or update query based on primery key name
     *
     * @return array|bool|mixed|string
     */
    public function save()
    {
        if (!empty($this->loadedFields) || $this->primaryKey() != null) {
            $save = $this->saveStatement();
            if ($save) {
                foreach ($this->onSaveSuccess as $index => $arr) {
                    $this->onSaveSuccess[$index]['function'](...$arr['args']);
                    $this->removeFromSaveSuccess($index);
                }
            } else {
                $this->_reverse();
                foreach ($this->onSaveError as $index => $arr) {
                    $this->onSaveError[$index]['function'](...$arr['args']);
                }
            }
            return $save;
        }
        return false;
    }

    /**
     * auto create save query and send this query to database
     *
     * @return array|bool|mixed|string
     */
    private function saveStatement()
    {
        if ($this->primaryKey() != null) {
            $alias = randid(6);
            $this->update([$alias => $this->_tableName()])
                ->where(DbDict::EQUAL, [$alias . '.' . $this->_primaryKeyName() => $this->primaryKey()]);

            $set = [];
            foreach ($this->loadedFields as $key => $field) {
                if (!in_array($key, $this->tableFields)) {
                    if ($field != $this->_primaryKeyName()) {
                        if (is_object($this->{$field})) {
                            if (get_class($this->{$field}) === 'Winged\Date\Date') {
                                $set[$field] = $this->{$field}->sql();
                            }
                        } else {
                            $set[$field] = $this->{$field};
                        }

                    }
                }
            }
            $this->set($set);
            $success = $this->build()->execute();
        } else {
            $this->insert()->into($this->_tableName());
            $into = [];
            foreach ($this->loadedFields as $key => $field) {
                if (is_object($this->{$field})) {
                    if (get_class($this->{$field}) === 'Winged\Date\Date') {
                        $into[$field] = $this->{$field}->sql();
                    }
                } else {
                    $into[$field] = $this->{$field};
                }
            }
            $this->values($into);
            $success = $this->build()->execute();
        }
        $this->_reverse();
        return $success;
    }

    /**
     * apply behaviors into values inside model
     *
     * @return $this
     */
    protected function _behaviors()
    {
        return $this->_apply();
    }

    /**
     * apply reverse behaviors into values inside model
     *
     * @return $this
     */
    public function _reverse()
    {
        return $this->_apply('_reverse');
    }

    /**
     * abstraction for apply reverse behaviors and behaviors on model
     *
     * @param string $type
     *
     * @return $this
     */
    protected function _apply($type = '_behaviors')
    {
        if ($type === '_behaviors') {
            $running = $this->behaviors();
            $push = 'parsedProperties';
            $unPush = 'reversedProperties';
        } else {
            $running = $this->reverseBehaviors();
            $push = 'reversedProperties';
            $unPush = 'parsedProperties';
        }
        foreach ($running as $key => $parsedValue) {
            $value_of_property_before_parse = $this->{$key};
            if (!property_exists($key, $this->{$push}) && property_exists(get_class($this), $key)) {
                if (is_callable($parsedValue)) {
                    $parsedValue = call_user_func($parsedValue);
                    $changeInsideCallable = false;
                    if ($this->{$key} !== $value_of_property_before_parse) {
                        $changeInsideCallable = true;
                    }
                    if (!$changeInsideCallable && ($parsedValue || !is_bool($parsedValue))) {
                        if ($parsedValue !== $this->{$key}) {
                            $this->{$key} = $parsedValue;
                            $this->{$push}->{$key} = $parsedValue;
                            $this->backup->{$key} = $value_of_property_before_parse;
                            $this->loadedFields[$key] = $key;
                            unset($this->{$unPush}->{$key});
                        }
                    } else {
                        if ($changeInsideCallable) {
                            $this->backup->{$key} = $value_of_property_before_parse;
                        }
                    }
                } else {
                    $this->{$key} = $parsedValue;
                    $this->{$push}->{$key} = $parsedValue;
                    $this->backup->{$key} = $value_of_property_before_parse;
                    $this->loadedFields[$key] = $key;
                    unset($this->{$unPush}->{$key});
                }
            }
        }
        return $this;
    }

    /**
     * register an callback for call when $this->save() obtain success
     *
     * @param string            $index
     * @param string | callable $function
     * @param array             $args
     *
     * @return bool
     */
    public function onSaveSuccess($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->onSaveSuccess[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    /**
     * register an callback for call when $this->save() obtain error
     *
     * @param string            $index
     * @param string | callable $function
     * @param array             $args
     *
     * @return bool
     */
    public function onSaveError($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->onSaveError[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    /**
     * remove an callback from callback stack for save success
     *
     * @param string $index
     */
    public function removeFromSaveSuccess($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->onSaveSuccess)) {
                unset($this->onSaveSuccess[$index]);
            }
        }
    }

    /**
     * remove an callback from callback stack for save error
     *
     * @param string $index
     */
    public function removeFromSaveError($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->onSaveError)) {
                unset($this->onSaveError[$index]);
            }
        }
    }

    /**
     * register an callback for call when $this->validate() obtain success
     *
     * @param string            $index
     * @param string | callable $function
     * @param array             $args
     *
     * @return bool
     */
    public function onValidateSuccess($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->onValidateSuccess[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    /**
     * register an callback for call when $this->validate() obtain error
     *
     * @param string            $index
     * @param string | callable $function
     * @param array             $args
     *
     * @return bool
     */
    public function onValidateError($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->onValidateError[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    /**
     * remove an callback from callback stack for validate success
     *
     * @param string $index
     */
    public function removeFromValidateSuccess($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->onValidateSuccess)) {
                unset($this->onValidateSuccess[$index]);
            }
        }
    }

    /**
     * remove an callback from callback stack for validate error
     *
     * @param string $index
     */
    public function removeFromValidateError($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->onValidateError)) {
                unset($this->onValidateError[$index]);
            }
        }
    }

    /**
     * get value from $_POST if value exists in $_POST, else get value from model internals
     *
     * @param string $name
     *
     * @return bool
     */
    public function postKey($name = '')
    {
        if (array_key_exists(get_class($this), $_POST)) {
            $arr = $_POST[get_class($this)];
            if (array_key_exists($name, $arr) && !property_exists($name, $this->reversedProperties)) {
                return $arr[$name];
            }
        }
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return false;
    }

    /**
     * * get value from $_GET if value exists in $_GET, else get value from model internals
     *
     * @param string $name
     *
     * @return bool
     */
    public function getKey($name = '')
    {
        if (array_key_exists(get_class($this), $_GET)) {
            $arr = $_GET[get_class($this)];
            if (array_key_exists($name, $arr) && !property_exists($name, $this->reversedProperties)) {
                return $arr[$name];
            }
        }
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return false;
    }

    /**
     * try to get value from any font
     *
     * @param string $name
     *
     * @return bool
     */
    public function requestKey($name = '')
    {
        $enter = $this->postKey($name) ? $this->postKey($name) : $this->getKey($name);
        if (!$enter) {
            if (property_exists($this, $name)) {
                return $this->{$name};
            }
        }
        return $enter;
    }

    /**
     * get errors occured when validate model
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * check if model have errors after validate
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!empty($this->errors)) {
            return true;
        }
        return false;
    }

}