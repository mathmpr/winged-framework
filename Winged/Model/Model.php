<?php

namespace Winged\Model;

use Winged\Database\DelegateQuery;
use Winged\Database\DbDict;
use Winged\Database\Database;
use Winged\Database\CurrentDB;
use Winged\Error\Error;

/**
 * Class Model
 * @package Winged\Model
 */
abstract class Model extends DelegateQuery
{
    public $errors = [];

    public $extras = false;

    private $on_save_error = [];

    private $on_save_success = [];

    private $on_validate_success = [];

    private $on_validate_error = [];

    private $parsed_properties = [];

    protected static $cached_info = [];


    /**
     * @return string
     */
    //abstract public static function primaryKeyName();

    /**
     * @return string
     */
    //abstract public static function tableName();

    /**
     * @param bool $pk
     * @return mixed
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
     * Labels used by Winged\Form\Form in components
     * @return array
     */
    abstract public function labels();


    public function __construct()
    {
        $this->getTableFields();
        if (!$this->extras) {
            $this->extras = new \stdClass();
        }
    }

    public function className()
    {
        return get_class($this);
    }

    public function hasOne($class_name, $link = [])
    {
        if (is_string($class_name) && is_array($link)) {
            if (array_key_exists('id', $link) && property_exists($this, $link['id'])) {
                if ((class_exists($class_name) && property_exists($class_name, $link['id'])) ||
                    (class_exists('\Winged\Model\\' . $class_name) && property_exists('\Winged\Model\\' . $class_name, $link['id']))
                ) {
                    $existent = class_exists($class_name) ? $class_name : '\Winged\Model\\' . $class_name;
                    /**
                     * @var $obj Model
                     */
                    $obj = (new $existent())
                        ->select()
                        ->from(['LINK' => $existent::tableName()])
                        ->where(DbDict::EQUAL, ['LINK.' . $link['id'] => $this->{$link['id']}]);

                    $result = $obj->one();
                    return $result;
                }
            }
        }
        return false;
    }

    public function hasMany($class_name, $link = [])
    {
        if (is_string($class_name) && is_array($link)) {
            if (array_key_exists('id', $link) && property_exists($this, $link['id'])) {
                if ((class_exists($class_name) && property_exists($class_name, $link['id'])) ||
                    (class_exists('\Winged\Model\\' . $class_name) && property_exists('\Winged\Model\\' . $class_name, $link['id']))
                ) {
                    $existent = class_exists($class_name) ? $class_name : '\Winged\Model\\' . $class_name;
                    /**
                     * @var $obj Model
                     */
                    $obj = (new $existent())
                        ->select()
                        ->from(['LINK' => $existent::tableName()])
                        ->where(DbDict::EQUAL, ['LINK.' . $link['id'] => $this->{$link['id']}]);

                    $result = $obj->find();
                    return $result;
                }
            }
        }
        return false;
    }

    public function old($property){
        if(array_key_exists($property, $this->before_values)){
            return $this->before_values[$property];
        }
        return false;
    }

    public function oldValueExists()
    {
        return $this->before_values_loaded;
    }

    public function isNew()
    {
        return $this->is_new;
    }

    public function extra()
    {
        return $this->extras;
    }

    public function unload($field)
    {
        if (in_array($field, $this->loaded_fields)) {
            unset($this->loaded_fields[array_search($field, $this->loaded_fields)]);
            return true;
        } else {
            return false;
        }
    }

    public function unloadAll()
    {
        foreach ($this->loaded_fields as $key => $value) {
            unset($this->loaded_fields[$key]);
        }
        return true;
    }

    public function loaded($field)
    {
        if (in_array($field, $this->table_fields)) {
            if (in_array($field, $this->loaded_fields)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function load($args = [], $newobj = false)
    {
        $fields_loaded_in_this_call = [];
        $class_name = get_class($this);
        $trade = false;
        if (empty($this->before_values) || count7($this->before_values) == 0) {
            $trade = true;
        }
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
        $rules = [];
        if (method_exists($this, 'rules')) {
            $rules = $this->rules();
            if (!is_array($rules)) {
                $rules = [];
            }
        }
        foreach ($prepared_data_to_load as $key => $value) {
            $value_of_property_before_parse = $this->{$key};
            $safe = false;
            if (array_key_exists($key, $rules)) {
                foreach ($rules[$key] as $name => $rule) {
                    if ($name === 'safe' || $rule === 'safe') {
                        $safe = true;
                    }
                }
            }

            if ($safe) {
                $this->safe_fields[] = $key;
            }

            if ((in_array(strtolower($key), $this->table_fields) || $safe) && property_exists($class_name, strtolower($key))) {
                $type = $this->returnMysqlType($key);
                $this->{$key} = $this->getRealValue($value, $type['key']);
                $this->loaded_fields[] = $key;
                $fields_loaded_in_this_call[] = $key;
                if ($trade && !array_key_exists($key, $this->before_values) && (!$this->isNew() || $newobj)) {
                    $this->before_values[strtolower($key)] = $value_of_property_before_parse;
                    $this->before_values_loaded = true;
                }
            }
        }

        $behaviors = $this->behaviors();

        foreach ($behaviors as $key => $parsed_value) {
            $value_of_property_before_parse = $this->{$key};
            $continue_and_apply_behavior = false;
            if ($this->isNew()) {
                $continue_and_apply_behavior = true;
            } else if (!$this->isNew() && in_array($key, $this->parsed_properties) && in_array($key, $fields_loaded_in_this_call)) {
                $continue_and_apply_behavior = true;
            }

            if ($continue_and_apply_behavior) {
                if (property_exists($class_name, $key)) {
                    if (is_callable($parsed_value)) {
                        $parsed_value = call_user_func($parsed_value);
                        if ($parsed_value !== null) {
                            if ($parsed_value !== $this->{$key}) {
                                if (!is_object($parsed_value)) {
                                    $type = $this->returnMysqlType($key);
                                    $parsed_value = $this->getRealValue($parsed_value, $type['key']);
                                }
                                $this->{$key} = $parsed_value;
                                $this->before_values[$key] = $value_of_property_before_parse;
                                $this->before_values_loaded = true;
                                $this->parsed_properties[] = $key;
                            }
                        }
                    } else {
                        if (!is_object($parsed_value)) {
                            $type = $this->returnMysqlType($key);
                            $parsed_value = $this->getRealValue($parsed_value, $type['key']);
                        }
                        $this->{$key} = $parsed_value;
                        $this->before_values[$key] = $value_of_property_before_parse;
                        $this->before_values_loaded = true;
                        $this->parsed_properties[] = $key;
                    }

                    if (in_array($key, $this->table_fields) && $value_of_property_before_parse != $this->{$key}) {
                        if (!in_array($key, $this->loaded_fields)) {
                            $this->loaded_fields[] = $key;
                        }
                    }
                }
            }
        }
        $this->is_new = false;
        return $this;
    }

    public function createOldValuesIfExists()
    {
        foreach ($this->table_fields as $key) {
            if ($this->{$key} != null) {
                $this->before_values[$key] = $this->{$key};
                $this->before_values_loaded = true;
            }
        }

        $this->is_new = false;

        return $this;
    }

    public function autoLoadDb($id = 0, $before_values = false)
    {
        $this->from_db = true;
        $this->is_new = false;
        $one = $this->findOne($id);
        if ($one) {
            foreach ($this->table_fields as $key) {
                $this->{$key} = $one->{$key};
            }
            if ($before_values) {
                $this->createOldValuesIfExists();
            }
            return true;
        }
        return false;
    }

    public function loadMultiple($args = [])
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
                    $model->load($arr);
                    $models[] = $model;
                }
            }
        }

        return $models;
    }


    public function pushValidateError($key, $error, $pn)
    {
        if (array_key_exists($key, $this->errors)) {
            $this->errors[$key][$pn] = $error;
        } else {
            $this->errors[$key] = [$pn => $error];
        }
        return true;
    }

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
                                                $reflect = new \ReflectionMethod(get_class($func[0]), $func[1]);
                                                $ret = $reflect->invokeArgs($func[0], $params);
                                            } else {
                                                Error::push(__CLASS__, "Object '" . $func[0] . "' no have method '" . $func[1] . "'", __FILE__, __LINE__);
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
            foreach ($this->on_validate_success as $index => $arr) {
                call_user_func_array($this->on_validate_success[$index]['function'], $arr['args']);
            }
        } else {
            $this->_reverse();
            foreach ($this->on_validate_error as $index => $arr) {
                call_user_func_array($this->on_validate_error[$index]['function'], $arr['args']);
            }
        }

        return $continue;
    }

    public function changeInLastUpdate()
    {
        return $this->change_in_last_update;
    }

    public function save()
    {
        if (!empty($this->loaded_fields) || $this->primaryKey() != null) {
            $save = $this->createSaveStatement();
            if ($save) {
                foreach ($this->on_save_success as $index => $arr) {
                    call_user_func_array($this->on_save_success[$index]['function'], $arr['args']);
                    $this->removeFromSaveSuccess($index);
                }
            } else {
                $this->_reverse();
                foreach ($this->on_save_error as $index => $arr) {
                    call_user_func_array($this->on_save_error[$index]['function'], $arr['args']);
                }
            }
            return $save;
        }
        return false;
    }

    public function getBeforeValue($property_name)
    {
        if (array_key_exists($property_name, $this->before_values)) {
            return $this->before_values[$property_name];
        }
        return null;
    }

    public function getTableFields()
    {
        $class_name = get_class($this);
        if (array_key_exists($class_name, self::$cached_info)) {
            $this->table_fields = self::$cached_info[$class_name]->table_fields;
            $this->table_info = self::$cached_info[$class_name]->table_info;
            return self::$cached_info[$class_name]->table_fields;
        } else {
            if (empty($this->table_fields) && $this->tableName() != '') {
                $all = CurrentDB::describe($this->tableName());
                foreach ($all as $key => $field) {
                    $this->table_fields[] = $key;
                    $this->table_info[$key] = $field;
                }
            }
            if (!array_key_exists($class_name, self::$cached_info)) {
                self::$cached_info[$class_name] = new \stdClass();
                self::$cached_info[$class_name]->table_fields = $this->table_fields;
                self::$cached_info[$class_name]->table_info = $this->table_info;
            }
            return $this->table_fields;
        }
    }

    public function _behaviors()
    {
        $class_name = get_class($this);
        $behaviors = $this->behaviors();
        foreach ($behaviors as $key => $apply) {
            if (property_exists($class_name, $key)) {
                if (is_callable($apply)) {
                    $apply = call_user_func($apply);
                }
                $this->{$key} = $apply;
            }
        }
    }

    public function _reverse()
    {
        if (!$this->reverse) {
            $this->reverse = true;
            $reverse = $this->reverseBehaviors();
            $class_name = get_class($this);
            foreach ($reverse as $key => $apply) {
                if (property_exists($class_name, $key)) {
                    if (is_callable($apply)) {
                        $apply = call_user_func($apply);
                    }
                    $this->{$key} = $apply;
                }
            }
        }
    }

    public function onSaveSuccess($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_save_success[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function onSaveError($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_save_error[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function removeFromSaveSuccess($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->on_save_success)) {
                unset($this->on_save_success[$index]);
            }
        }
    }

    public function removeFromSaveError($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->on_save_error)) {
                unset($this->on_save_error[$index]);
            }
        }
    }

    public function onValidateSuccess($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_validate_success[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function onValidateError($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_validate_error[$index] = [
                'function' => \Closure::bind($function, $this, get_class()),
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function removeFromValidateSuccess($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->on_validate_success)) {
                unset($this->on_validate_success[$index]);
            }
        }
    }

    public function removeFromValidateError($index = '')
    {
        if (is_int($index) || is_string($index)) {
            if (array_key_exists($index, $this->on_validate_error)) {
                unset($this->on_validate_error[$index]);
            }
        }
    }

    public function postKey($name = '')
    {
        $reverse = $this->reverseBehaviors();
        if (array_key_exists(get_class($this), $_POST)) {
            $arr = $_POST[get_class($this)];
            if (array_key_exists($name, $arr) && !array_key_exists($name, $reverse)) {
                return $arr[$name];
            }
        }
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return false;
    }

    public function getKey($name = '')
    {
        $reverse = $this->reverseBehaviors();
        if (array_key_exists(get_class($this), $_GET)) {
            $arr = $_GET[get_class($this)];
            if (array_key_exists($name, $arr) && !array_key_exists($name, $reverse)) {
                return $arr[$name];
            }
        }
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return false;
    }

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

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        if (!empty($this->errors)) {
            return true;
        }
        return false;
    }

    public function pushExtra($index, $value)
    {
        return $this->extras->$index = $value;
    }
}