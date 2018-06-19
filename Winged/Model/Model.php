<?php

namespace Winged\Model;

use Winged\Database\DelegateQuery;
use Winged\Database\DbDict;
use Winged\Database\Database;
use Winged\Database\CurrentDB;
use Winged\Error\Error;

class Model extends DelegateQuery
{
    public $errors = [];

    public $extras = false;

    private $on_save_error = [];

    private $on_save_success = [];

    private $on_validate_success = [];

    private $on_validate_error = [];

    protected static $cached_info = [];

    public function __construct()
    {
        $this->getTableFields();
        if (!$this->extras) {
            $this->extras = new \stdClass();
        }
    }

    public function dynamic($key, $value)
    {
        $this->{$key} = $value;
        return $this;
    }

    public function setProperty(array $array, $dynamic = false)
    {
        $refl = new \ReflectionClass($this);
        foreach ($array as $setp => $value) {
            if (property_exists(get_class($this), $setp)) {
                $property = $refl->getProperty($setp);
                if ($property instanceof \ReflectionProperty) {
                    $property->setValue($this, $value);
                }
            } else {
                if ($dynamic) {
                    $this->dynamic($setp, $value);
                }
            }
        }
        return $this;
    }

    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            $refl = new \ReflectionClass($this);
            $property = $refl->getProperty($name);
            return $property->getValue($this);
        }
        return false;
    }

    public function className()
    {
        return get_class($this);
    }

    public function hasOne($class_name, $link = [])
    {
        if (is_string($class_name) && is_array($link)) {
            if (array_key_exists('id', $link) && property_exists($this, $link['id'])) {
                if (class_exists($class_name) && property_exists($class_name, $link['id'])) {
                    /**
                     * @var $obj Model
                     */
                    $obj = (new $class_name())
                        ->select()
                        ->from(['LINK' => $class_name::tableName()])
                        ->where(DbDict::EQUAL, ['LINK.' . $link['id'] => $this->getProperty($link['id'])]);

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
                if (class_exists($class_name) && property_exists($class_name, $link['id'])) {
                    /**
                     * @var $obj Model
                     */
                    $obj = (new $class_name())
                        ->select()
                        ->from(['LINK' => $class_name::tableName()])
                        ->where(DbDict::EQUAL, ['LINK.' . $link['id'] => $this->getProperty($link['id'])]);

                    $result = $obj->find();
                    return $result;
                }
            }
        }
        return false;
    }

    public function oldValueExists()
    {
        return $this->old_value_loaded;
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
        foreach ($this->loaded_fields as $key => $value){
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
        $class_name = get_class($this);

        $trade = false;

        if (empty($this->old_values) || count7($this->old_values) == 0) {
            $trade = true;
        }

        $to_load = [];

        foreach ($args as $key => $value) {
            if (is_array($value) && ucfirst($key) == $class_name) {
                if (array_key_exists(0, $value)) {
                    $to_load = $value[0];
                } else {
                    $to_load = $value;
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

        foreach ($to_load as $key => $value) {

            $old_value = $this->{$key};

            $safe = false;

            if (array_key_exists($key, $rules)) {
                foreach ($rules[$key] as $name => $rule) {
                    if ($name === 'safe' || $rule === 'safe') {
                        $safe = true;
                    }
                }
            }

            if ((in_array(strtolower($key), $this->table_fields) || $safe) && property_exists($class_name, strtolower($key))) {
                $type = $this->returnMysqlType($key);
                $this->{$key} = $this->getRealValue($value, $type['key']);
                $this->loaded_fields[] = $key;
                if ($safe) {
                    $this->safe_fields[] = $key;
                }
                if ($trade && !array_key_exists($key, $this->old_values) && (!$this->is_new || $newobj)) {
                    $this->old_values[strtolower($key)] = $old_value;
                    $this->old_value_loaded = true;
                }
            }
        }

        $behaviors = $this->behaviors();

        foreach ($behaviors as $key => $apply) {

            $safe = false;

            $old_value = $this->{$key};

            if (array_key_exists($key, $rules)) {
                foreach ($rules[$key] as $name => $rule) {
                    if ($name === 'safe' || $rule === 'safe') {
                        $safe = true;
                    }
                }
            }

            if (property_exists($class_name, $key)) {
                if (is_callable($apply)) {
                    $apply = call_user_func($apply);
                    if (is_array($apply) && $apply['null'] === null) {
                        $apply = null;
                        $this->{$key} = $apply;
                        $this->old_values[$key] = $old_value;
                        $this->old_value_loaded = true;
                    }
                    if ($apply !== null) {
                        if ($apply !== $this->{$key}) {
                            if (!is_object($apply)) {
                                $type = $this->returnMysqlType($key);
                                $apply = $this->getRealValue($apply, $type['key']);
                            }
                            $this->{$key} = $apply;
                            $this->old_values[$key] = $old_value;
                            $this->old_value_loaded = true;
                        }
                    }
                } else {
                    if (!is_object($apply)) {
                        $type = $this->returnMysqlType($key);
                        $apply = $this->getRealValue($apply, $type['key']);
                    }
                    $this->{$key} = $apply;
                    $this->old_values[$key] = $old_value;
                    $this->old_value_loaded = true;
                }

                if (in_array($key, $this->table_fields)) {
                    if (!in_array($key, $this->loaded_fields)) {
                        $this->loaded_fields[] = $key;
                        if ($safe) {
                            $this->safe_fields[] = $key;
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
        $class_name = get_class($this);
        $reflection = new \ReflectionClass($class_name);
        foreach ($this->table_fields as $key) {
            $value = $reflection->getProperty(strtolower($key))->getValue($this);
            if ($value != null) {
                $this->old_values[$key] = $value;
                $this->old_value_loaded = true;
            }
        }

        $this->is_new = false;

        return $this;
    }

    public function autoLoadDb($id = 0, $old = false)
    {
        $this->from_db = true;
        $this->is_new = false;
        $one = $this->findOne($id);
        $class_name = get_class($this);
        $reflection = new \ReflectionClass($class_name);
        if ($one) {
            foreach ($this->table_fields as $key) {
                $value = $reflection->getProperty(strtolower($key))->getValue($one);
                $reflection->getProperty(strtolower($key))->setValue($this, $value);
            }
            if ($old) {
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
        $to_load = [];
        foreach ($args as $key => $value) {
            if (is_array($value) && ucfirst($key) == $class_name) {
                if (array_key_exists(0, $value)) {
                    $to_load = $value;
                } else {
                    $to_load = array($value);
                }
            }
        }

        if (is_array($to_load)) {
            if (count7($to_load) === 1) {
                $to_load = $to_load[0];
                $count = 0;
                foreach ($to_load as $key => $value) {
                    if (count7($value) > $count) {
                        $count = count7($value);
                    }
                }
                for ($x = 0; $x < $count; $x++) {
                    $arr = [
                        $class_name => []
                    ];
                    foreach ($to_load as $key => $value) {
                        if (array_key_exists($x, $to_load[$key])) {
                            $arr[$class_name][$key] = $to_load[$key][$x];
                        }
                    }
                    $model = new $class_name();
                    $model->load($arr);
                    $models[] = $model;
                }
            }
        }

        return $models;
    }


    public function pushValidateError($key, $error = '')
    {
        if (array_key_exists($key, $this->errors)) {
            $this->errors[$key][] = $error;
        } else {
            $this->errors[$key] = [$error];
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
                                ['text' => 'Field ' . $property_rule . ' is required.', 'filter' => false],
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
                                }
                                if ($test) {
                                    $message = $erros[$pn]['text'];
                                    if (array_key_exists($property_rule, $messages)) {
                                        if (array_key_exists($pn, $messages[$property_rule])) {
                                            $message = $messages[$property_rule][$pn];
                                        }
                                    }
                                    if ($erros[$pn]['filter']) {
                                        if (!filter_var($this->{$property_rule}, $erros[$pn]['filter'])) {
                                            $continue = false;
                                            $this->pushValidateError($property_rule, $message);
                                        }
                                    } else if ($this->{$property_rule} === null || $this->{$property_rule} === false || $this->{$property_rule} === '') {
                                        $continue = false;
                                        $this->pushValidateError($property_rule, $message);
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
                                        $this->pushValidateError($property_rule, $message);
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
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'email' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_EMAIL)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'float' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_FLOAT)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'int' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_INT)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'ip' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_IP)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'url' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_URL)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                            if ($rule == 'bool' && !filter_var($this->{$property_rule}, FILTER_VALIDATE_BOOLEAN)) {
                                $this->pushValidateError($property_rule, $message);
                                $continue = false;
                            }
                        }
                    }
                }
            }
        }

        if ($continue) {
            foreach ($this->on_validate_success as $index => $arr) {
                call_user_func_array($arr['function'], $arr['args']);
            }
        } else {
            foreach ($this->on_validate_error as $index => $arr) {
                call_user_func_array($arr['function'], $arr['args']);
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
                    call_user_func_array($arr['function'], $arr['args']);
                }
            } else {
                foreach ($this->on_save_error as $index => $arr) {
                    call_user_func_array($arr['function'], $arr['args']);
                }
            }
            return $save;
        }
        return false;
    }

    public function getOldValue($property_name)
    {
        if (array_key_exists($property_name, $this->old_values)) {
            return $this->old_values[$property_name];
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
                $all = $table = CurrentDB::sp(Database::SP_DESC_TABLE, ['table_name' => $this->tableName()]);
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

    public function registerOnSaveSuccess($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_save_success[$index] = [
                'function' => $function,
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function registerOnSaveError($index = '', $function = '', $args = [])
    {
        if (is_callable($function) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_save_error[$index] = [
                'function' => $function,
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

    public function registerOnValidateSuccess($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_validate_success[$index] = [
                'function' => $function,
                'args' => $args
            ];
            return true;
        }
        return false;
    }

    public function registerOnValidateError($index = '', $function = '', $args = [])
    {
        if ((is_callable($function) || is_array($function)) && is_array($args) && (is_int($index) || is_string($index))) {
            $this->on_validate_error[$index] = [
                'function' => $function,
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
        if (array_key_exists(get_class($this), $_POST)) {
            $arr = $_POST[get_class($this)];
            if (array_key_exists($name, $arr)) {
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
        if (array_key_exists(get_class($this), $_GET)) {
            $arr = $_GET[get_class($this)];
            if (array_key_exists($name, $arr)) {
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

    public function getErros()
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

    public static function primaryKeyName()
    {
        return '';
    }

    public function primaryKey()
    {
        return 0;
    }

    public function pushExtra($index, $value)
    {
        return $this->extras->$index = $value;
    }

    public static function tableName()
    {
        return '';
    }

    public function behaviors()
    {
        return [];
    }

    public function reverseBehaviors()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }

}