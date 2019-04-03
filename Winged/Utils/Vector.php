<?php

namespace Winged\Utils;

use Winged\Error\Error;

/**
 * Class Vector
 */
class Vector implements \ArrayAccess, \Iterator
{
    private $position = null;
    private $vector = [];
    private $hashs = [];
    private $objectOffsets = [];
    private $needsReset = false;

    public static function factory($vector = [])
    {
        return new Vector($vector);
    }

    public function __construct($vector = [])
    {
        $this->position = 0;
        $this->setVector($vector);
    }

    /**
     * return offset has string if offset is an object
     * @param $offset
     * @return bool|string
     */
    public function stringOffset($offset)
    {
        if ($this->keyExists($offset)) {
            if (is_object($offset)) {
                $offset = spl_object_hash($offset);
            }
            return $offset;
        }
        return false;
    }

    /**
     * return offset has object if offset exists inside $this->objectOffsets
     * @param $offset
     * @return bool|mixed|string
     */
    public function objectOffset($offset)
    {
        if ($this->keyExists($offset)) {
            if (is_object($offset)) {
                $offset = spl_object_hash($offset);
            }
            if (array_key_exists($offset, $this->objectOffsets)) {
                return $this->objectOffsets[$offset];
            }
            return $offset;
        }
        return false;
    }

    /**
     * check if offset exists inside vector
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_object($offset)) {
            $offset = spl_object_hash($offset);
        }
        if (array_key_exists($offset, $this->vector)) {
            return true;
        }
        return false;
    }

    /**
     * check if offset exists inside vector
     * @param $offset
     * @return bool
     */
    public function keyExists($offset)
    {
        return $this->offsetExists($offset);
    }

    /**
     * return an value linked with offset if offset exists
     * @param $offset
     * @return bool|mixed
     */
    public function offsetGet($offset)
    {
        $offset = $this->stringOffset($offset);
        if (array_key_exists($offset, $this->vector)) {
            return $this->vector[$offset];
        }
        return false;
    }

    /**
     * return an value linked with offset if offset exists
     * @param $offset
     * @return bool|mixed
     */
    public function get($offset)
    {
        return $this->offsetGet($offset);
    }

    /**
     * set value linked to the offset
     * offset can be an object
     * if value is an object, the value is the only one inside vector
     * other equal object can't be pass to set() ou offsetSet()
     * @param $offset
     * @param $value
     * @return bool
     */
    public function offsetSet($offset, $value)
    {
        if (is_object($value)) {
            if ($this->keyByValue($value)->count() > 0) {
                return false;
            }
            $this->hashs[] = spl_object_hash($value);
        }

        if (is_object($offset)) {
            $_offset = spl_object_hash($offset);
            $this->objectOffsets[$_offset] = $offset;
            $offset = $_offset;
        }

        if (is_scalar($offset)) {
            $this->vector[$offset] = $value;
        } else {
            if (!$offset) {
                $this->vector[] = $value;
            }
        }
        $this->position = $this->key();
        return true;
    }

    /**
     * set value linked to the offset
     * offset can be an object
     * @param $offset
     * @param bool $value
     * @return bool
     */
    public function set($offset, $value = false)
    {
        return $this->offsetSet($offset, $value);
    }

    /**
     * unset value liked with offset
     * offset can be an object
     * @param mixed $offset
     * @return bool
     */
    public function offsetUnset($offset)
    {
        $offset = $this->stringOffset($offset);
        if (array_key_exists($offset, $this->vector)) {
            if (key($this->vector) === $offset) {
                if ($this->next()) {
                    $this->position = key($this->vector);
                } else if ($this->prev()) {
                    $this->position = key($this->vector);
                }
            }
            if (is_object($this->vector[$offset])) {
                $hash = spl_object_hash($this->vector[$offset]);
                $search = array_search($hash, $this->hashs, true);
                if ($search || is_int($search)) {
                    unset($this->hashs[$search]);
                }
            }
            unset($this->vector[$offset]);
            if (array_key_exists($offset, $this->objectOffsets)) {
                unset($this->objectOffsets[$offset]);
            }
            return true;
        }
        return false;
    }

    /**
     * unset all values linked with offsets passed
     * offset inside offsets can be an object
     * @param $offsets
     * @return bool
     */
    public function unsetAll($offsets)
    {
        $unset = true;
        if (!is_array($offsets)) {
            if (is_object($offsets) && get_class($offsets) === 'stdClass') {
                $offsets = (array)DeepClone::factory($offsets)->copy();
            }
        }
        if (is_array($offsets)) {
            foreach ($offsets as $value) {
                if (!$this->offsetUnset($value)) {
                    $unset = false;
                }
            }
        }
        return $unset;
    }

    /**
     * check if value exists inside vector
     * value can be an object
     * @param $value
     * @return bool
     */
    public function contains($value)
    {
        if (is_object($value)) {
            return in_array(spl_object_hash($value), $this->hashs);
        }
        return in_array($value, $this->vector);
    }

    /**
     * return a key linked with values
     * value can be an object
     * @param $value
     * @return Vector
     */
    public function keyByValue($value)
    {
        $keys = array_filter($this->vector, function ($v) use ($value) {
            $offset = -1;
            $valueOffset = -2;
            if (is_object($v)) {
                $offset = spl_object_hash($v);
            }
            if (is_object($value)) {
                $valueOffset = spl_object_hash($value);
            }
            if ($v === $value || $offset === $valueOffset) {
                return true;
            }
            return false;
        });
        $_keys = Vector::factory([]);
        if (!empty($keys)) {
            foreach ($keys as $key => $value) {
                if (array_key_exists($key, $this->objectOffsets)) {
                    $_keys[] = $this->objectOffsets[$key];
                } else {
                    $_keys[] = $key;
                }
            }
        }
        return $_keys;
    }

    /**
     * return all keys linked with values passed
     * value inside values can be an object
     * @param array $values
     * @return array
     */
    public function keyByValueAll($values = [])
    {
        $keys = Vector::factory([]);
        if (!is_array($values)) {
            if (is_object($values) && get_class($values) === 'stdClass') {
                $values = (array)DeepClone::factory($values)->copy();
            }
        }
        if (is_array($values)) {
            foreach ($values as $value) {
                $keys->setVector(array_merge($keys->getVector(), $this->keyByValue($value)));
            }
        }
        return $keys;
    }

    /**
     * unset vector by value passed
     * value can be an object
     * @param $value
     */
    public function unsetByValue($value)
    {
        $keys = $this->keyByValue($value);
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $this->offsetUnset($key);
            }
        }
    }

    /**
     * unset all offsets in vector by passed values
     * value inside values can be an object
     * @param $values array
     */
    public function unsetByValueAll($values = [])
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                $this->unsetByValue($value);
            }
        }
    }

    /**
     * check if all keys exists in vector
     * offset inside offsets can be an object
     * @param array $offsets
     * @return bool
     */
    public function keyExistsAll($offsets = [])
    {
        if (!is_array($offsets)) {
            if (is_object($offsets) && get_class($offsets) === 'stdClass') {
                $offsets = (array)DeepClone::factory($offsets)->copy();
            }
        }
        if (is_array($offsets)) {
            foreach ($offsets as $offest) {
                if (!$this->keyExists($offest)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * get all values from vector with founded offsets
     * offset inside offsets can be an object
     * @param array $offsets
     * @return array
     */
    public function getAll($offsets = [])
    {
        $return = Vector::factory([]);
        if (!is_array($offsets)) {
            if (is_object($offsets) && get_class($offsets) === 'stdClass') {
                $offsets = (array)DeepClone::factory($offsets)->copy();
            }
        }
        if (is_array($offsets)) {
            foreach ($offsets as $offest) {
                $get = $this->get($offest);
                $exists = $this->offsetExists($offest);
                if ($get || $exists) {
                    $return[$offest] = $get;
                }
            }
        }
        return $return;
    }

    /**
     * return vector as array
     * @return array
     */
    public function getVector()
    {
        return $this->vector;
    }

    /**
     * put an array inside vector object
     * @param array $vector
     */
    public function setVector($vector = [])
    {
        if (is_array($vector)) {
            $this->vector = $vector;
        } else if (is_object($vector)) {
            $this->vector = (array)$vector;
        } else {
            $this->vector[] = $vector;
        }
    }

    /**
     * @param array|object $vector
     * @param array $stack
     * @return array|bool|callable
     * @throws \ReflectionException
     */
    private function getVectorAllRecursive($vector = [], &$stack = [])
    {
        if ($vector) {
            if (is_object($vector) && !is_callable($vector)) {
                if (!in_array(spl_object_hash($vector), $stack)) {
                    $stack[] = spl_object_hash($vector);
                    if (get_class($vector) === 'stdClass') {
                        foreach ($vector as $key => $value) {
                            if (get_class($value) === __CLASS__) {
                                $vector->{$key} = $value->getVectorAll();
                            }
                        }
                        return $vector;
                    } else {
                        $reflection = new \ReflectionClass($vector);
                        $properties = $reflection->getProperties();
                        if ($properties) {
                            foreach ($properties as $property) {
                                $private = false;
                                if ($property->isPrivate()) {
                                    $property->setAccessible(true);
                                    $private = true;
                                }

                                $get = $property->getValue($vector);
                                if (get_class($get) === __CLASS__) {
                                    $property->setValue($vector, $get->getVectorAll());
                                }

                                if ($private) {
                                    $property->setAccessible(false);
                                }
                            }
                        }
                    }
                }
                return $vector;
            } else if (is_array($vector)) {
                foreach ($vector as $key => $value) {
                    if (get_class($value) === __CLASS__) {
                        $vector[$key] = $value->getVectorAll();
                    }
                }
            } else {
                return $vector;
            }
            return $vector;
        }
        return false;
    }

    /**
     * return vector array inside this object and parse all others vector object inside this vector object into array
     * @return array
     * @throws \ReflectionException
     */
    public function getVectorAll()
    {
        return $this->getVectorAllRecursive($this->vector);
    }

    /**
     * set vector inside this object and parse all other array in vector object
     * @param null|array $vector
     */
    public function setVectorAll($vector = null)
    {
        if ($vector) {
            $this->setVector($vector);
        }
        foreach ($this->vector as $key => $value) {
            if (is_array($value)) {
                $this->vector[$key] = Vector::factory($value);
                $this->vector[$key]->setVectorAll();
            }
        }
    }

    /**
     * check if offset is valid
     * @return bool
     */
    function valid()
    {
        return isset($this->vector[$this->position]);
    }

    /**
     * return current key of vector
     * @return int|mixed|string|null
     */
    function key()
    {
        if ($this->needsReset) {
            $this->rewind();
        }
        $key = key($this->vector);
        if (array_key_exists($key, $this->objectOffsets)) {
            return $this->objectOffsets[$key];
        }
        return $key;
    }

    /**
     * go to next position in vector
     * @return bool|mixed
     */
    public function next()
    {
        $next = next($this->vector);
        $this->position = key($this->vector);
        if ($next) {
            return $next;
        } else {
            $this->needsReset = true;
            return false;
        }
    }

    /**
     * go to prev element in vector
     * @return bool|mixed
     */
    public function prev()
    {
        $prev = prev($this->vector);
        $this->position = key($this->vector);
        if ($prev) {
            return $prev;
        } else {
            return false;
        }
    }

    /**
     * return current element of internal pointer of vector
     * @return mixed
     */
    public function current()
    {
        return current($this->vector);
    }

    /**
     * reset position of vector
     * @return bool
     */
    public function rewind()
    {
        reset($this->vector);
        $this->position = key($this->vector);
        return true;
    }

    /**
     * return first key of vector
     * @return mixed
     */
    public function firstKey()
    {
        return array_key_first($this->vector);
    }

    /**
     * return last key of vector
     * @return mixed
     */
    public function lastKey()
    {
        return array_key_last($this->vector);
    }

    /**
     * return last element of vector
     * @return bool|mixed
     */
    public function end()
    {
        $key = $this->lastKey();
        if ($key) {
            return $this->get($key);
        }
        return false;
    }

    /**
     * return fist element of vector
     * @return bool|mixed
     */
    public function begin()
    {
        $key = $this->firstKey();
        if ($key) {
            return $this->get($key);
        }
        return false;
    }

    /**
     * return count of elements of vector
     * @return int
     */
    public function count()
    {
        return count($this->vector);
    }

    /**
     * check if vector is empty
     * @return bool
     */
    public function isEmpty()
    {
        return !(count($this->vector) > 0);
    }

    /**
     * @param $function
     * @param array $stack
     * @param array|object $runIn
     * @param string $from
     * @return array|callable
     * @throws \ReflectionException
     */
    private function _walk($function, $runIn = [], &$stack = [], $from = '')
    {
        if (!array_key_exists(spl_object_id($this), $stack)) {
            $stack[spl_object_id($this)] = &$this;
        }
        if (is_callable($function) && $runIn) {
            if ((is_object($runIn) || is_array($runIn)) && !is_callable($runIn)) {
                if (is_object($runIn)) {
                    if (!array_key_exists(spl_object_id($runIn), $stack)) {
                        $stack[spl_object_id($runIn)] = &$runIn;
                        if (get_class($runIn) === 'stdClass') {
                            foreach ($runIn as $key => $value) {
                                if (is_object($value) && get_class($value)) {
                                    if (array_key_exists(spl_object_id($value), $stack)) {
                                        continue;
                                    }
                                }
                                $get = $this->_walk($function, $value, $stack, $key);
                                if ($get) {
                                    $runIn->{$key} = $get;
                                }
                            }
                        } else {
                            $reflection = (new \ReflectionClass(get_class($runIn)));
                            $properties = $reflection->getProperties();
                            if ($properties) {
                                foreach ($properties as $property) {
                                    $private = false;
                                    if ($property->isProtected() || $property->isPrivate() || $property->isPublic()) {
                                        if ($property->isPrivate() || $property->isProtected()) {
                                            $property->setAccessible(true);
                                            $private = true;
                                        }
                                    }

                                    $get = $property->getValue();

                                    if (is_object($get) && get_class($get) === __CLASS__) {
                                        if (!array_key_exists(spl_object_id($get), $stack)) {
                                            $get->walkDeep($function);
                                        }
                                    } else {
                                        $get = $this->_walk($function, $get, $stack, $property->getName());
                                        if ($get) {
                                            $property->setValue($runIn, $get);
                                        }
                                    }

                                    if ($private) {
                                        $property->setAccessible(false);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    foreach ($runIn as $key => &$value) {
                        if (is_object($value) && get_class($value) === __CLASS__) {
                            if (!array_key_exists(spl_object_id($value), $stack)) {
                                $runIn[$key]->walkDeep($function);
                            }
                        } else {
                            if (is_object($value) && get_class($value)) {
                                if (array_key_exists(spl_object_id($value), $stack)) {
                                    continue;
                                }
                            }
                            $get = $this->_walk($function, $value, $stack, $key);
                            if ($get) {
                                $runIn[$key] = $get;
                            }
                        }
                    }
                }
            } else {
                $objectKey = null;
                if (array_key_exists($from, $this->objectOffsets)) {
                    $objectKey = $this->objectOffsets[$from];
                }
                $get = call_user_func_array($function, [
                    'value' => $runIn,
                    'key' => $from,
                    'objectKey' => $objectKey
                ]);
                if ($get) {
                    return $get;
                }
            }
        }
        return $runIn;
    }

    /**
     * @param $function
     * @param $runIn
     * @param $from
     * @return mixed
     */
    private function _executeWalkCallback($function, $runIn, $from)
    {
        $objectKey = null;
        if (array_key_exists($from, $this->objectOffsets)) {
            $objectKey = $this->objectOffsets[$from];
        }
        $get = call_user_func_array($function, [
            'value' => $runIn,
            'key' => $from,
            'objectKey' => $objectKey
        ]);
        return $get;
    }

    /**
     * @param $function
     * @param array $runIn
     * @return array
     * @throws \ReflectionException
     */
    private function _walkOne($function, $runIn = [])
    {
        if (is_callable($function) && $runIn) {
            if ((is_object($runIn) || is_array($runIn)) && !is_callable($runIn)) {
                if (is_object($runIn)) {
                    if (get_class($runIn) === 'stdClass') {
                        foreach ($runIn as $key => $value) {
                            if (!is_object($value) && !is_array($value)) {
                                $get = $this->_executeWalkCallback($function, $value, $key);
                                if ($get) {
                                    $runIn->{$key} = $get;
                                }
                            }
                        }
                    } else {
                        $reflection = (new \ReflectionClass(get_class($runIn)));
                        $properties = $reflection->getProperties();
                        if ($properties) {
                            foreach ($properties as $property) {
                                $private = false;
                                if ($property->isProtected() || $property->isPrivate() || $property->isPublic()) {
                                    if ($property->isPrivate() || $property->isProtected()) {
                                        $property->setAccessible(true);
                                        $private = true;
                                    }
                                }
                                $get = $property->getValue();
                                if (!is_object($get) && !is_array($get)) {
                                    $get = $this->_executeWalkCallback($function, $get, $property->getName());
                                    if ($get) {
                                        $property->setValue($runIn, $get);
                                    }
                                }
                                if ($private) {
                                    $property->setAccessible(false);
                                }
                            }
                        }
                    }
                } else {
                    foreach ($runIn as $key => $value) {
                        if (!is_object($value) && !is_array($value)) {
                            $get = $this->_executeWalkCallback($function, $value, $key);
                            if ($get) {
                                $runIn[$key] = $get;
                            }
                        }
                    }
                }
            }
        }
        return $runIn;
    }

    /**
     * execute function on elements inside vector and if function return an value, the vector in position replace with it
     * if the current element is an array or object walk is executed again
     * function is applied into properties inside objects including private properties
     * @param callable $function
     * @throws \ReflectionException
     */
    public function walkDeep($function = null)
    {
        if (is_callable($function)) {
            if (is_callable($function)) {
                $this->vector = $this->_walk($function, $this->vector);
            }
        }
    }

    /**
     * execute function on elements inside vector and if function return an value, the vector in position replace with it
     * @param callable $function
     * @throws \ReflectionException
     */
    public function walk($function = null)
    {
        if (is_callable($function)) {
            $this->vector = $this->_walkOne($function, $this->vector);
        }
    }

    /**
     * remove the last element of vector and return it
     * @return bool|mixed
     */
    public function pop()
    {
        $value = $this->end();
        $key = $this->lastKey();
        if ($value && $key) {
            $value = $this->get($key);
            $this->offsetUnset($key);
            return $value;
        }
        return false;
    }

    /**
     * remove the first element of vector and return it
     * @return bool|mixed
     */
    public function popBegin()
    {
        $value = $this->begin();
        $key = $this->firstKey();
        if ($value && $key) {
            $value = $this->get($key);
            $this->offsetUnset($key);
            return $value;
        }
        return false;
    }

    /**
     * push value on end of vector
     * @param $value
     * @return bool|mixed
     */
    public function push($value)
    {
        array_push($this->vector, $value);
        return $this->end();
    }

    /**
     * push an value on begin vector
     * @param $value
     * @return bool|mixed
     */
    public function pushBegin($value)
    {
        $this->vector = array_merge([$value], $this->vector);
        return $this->begin();
    }

    /**
     * execute an function inside all elements of vector and return the joined value
     * @param callable $function
     * @param bool|mixed $initial
     * @return bool|mixed
     */
    public function reduce($function, $initial = false)
    {
        if (is_callable($function)) {
            $reduced = array_reduce($this->vector, function ($carry, $item) use ($function, $initial) {
                if ($initial) {
                    return $function($carry, $item, $initial);
                } else {
                    return $function($carry, $item, $initial);
                }
            }, $initial);
            return $reduced;
        }
        return false;
    }

    /**
     * remove all duplicated values in vector
     */
    public function unique()
    {
        $keys = [];
        foreach ($this->vector as $value) {
            $_keys = $this->keyByValue($value);
            if ($_keys->count() > 1) {
                foreach ($_keys as $_key => $key) {
                    if ($_key != 0) {
                        $keys[] = $key;
                    }
                }
            }
        }
        foreach ($keys as $key) {
            if ($this->offsetExists($key)) {
                $this->offsetUnset($key);
            }
        }
    }

    /**
     * @param string|object|int|float $start
     * @param int $deleteCount
     * @param bool|string|int|float|array|object $value
     * @return Vector
     */
    public function splice($start, $deleteCount, $value = false)
    {
        $count = 0;
        $remove = false;
        $removed = Vector::factory([]);
        if ($this->stringOffset($start)) {
            $start = $this->stringOffset($start);
            foreach ($this->vector as $key => $_value) {
                if ($key === $start) {
                    $remove = true;
                }
                if ($remove) {
                    $removed[$this->objectOffset($key)] = $this->get($key);
                    $this->offsetUnset($key);
                    $count++;
                }

                if ($count === $deleteCount) {
                    $remove = false;
                    if($value){
                        if (is_array($value)) {
                            foreach ($value as $_key => $v) {
                                $this->offsetSet($_key, $v);
                            }
                        } else {
                            $this->push($value);
                        }
                    }
                }
            }
        }
        return $removed;
    }

    /**
     * @param mixed|object $columnKey
     * @param null $indexKey
     * @return bool|Vector
     */
    public function column($columnKey, $indexKey = null)
    {
        if (is_object($columnKey)) {
            $columnKey = spl_object_hash($columnKey);
        }
        if (is_object($indexKey)) {
            $indexKey = spl_object_hash($indexKey);
        }
        $arrays = $this->getFiltered(['array', 'object']);
        $array = Vector::factory([]);
        /**
         * @var $value Vector
         */
        foreach ($arrays as $value) {
            if (!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    /**
     * get parts of vector with matched types
     * @param array $types
     * @return array
     */
    public function getFiltered($types = [])
    {
        $parts = Vector::factory([]);
        foreach ($this->vector as $key => $value) {
            if (is_array($value) && in_array('array', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_resource($value) && in_array('resource', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_object($value) && in_array('object', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_int($value) && in_array('int', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_float($value) && in_array('float', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_double($value) && in_array('double', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_string($value) && in_array('string', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_bool($value) && in_array('bool', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
            if (is_null($value) && in_array('null', $types)) {
                $parts[$this->objectOffset($key)] = $value;
            }
        }
        return $parts;
    }

    /**
     * return all arrays inside vector
     * @return array
     */
    public function getArraysInside()
    {
        return $this->getFiltered(['array']);
    }

    /**
     * return all resources inside vector
     * @return array
     */
    public function getResourcesInside()
    {
        return $this->getFiltered(['resource']);
    }

    /**
     * return all objects inside vector
     * @return array
     */
    public function getObjectsInside()
    {
        return $this->getFiltered(['object']);
    }

    /**
     * return all integers inside vector
     * @return array
     */
    public function getIntegersInside()
    {
        return $this->getFiltered(['int']);
    }

    /**
     * return all floats inside vector
     * @return array
     */
    public function getFloatsInside()
    {
        return $this->getFiltered(['float']);
    }

    /**
     * return all doubles inside vector
     * @return array
     */
    public function getDoublesInside()
    {
        return $this->getFiltered(['double']);
    }

    /**
     * return all strings inside vector
     * @return array
     */
    public function getStringsInside()
    {
        return $this->getFiltered(['string']);
    }

    /**
     * return all booleans inside vector
     * @return array
     */
    public function getBooleansInside()
    {
        return $this->getFiltered(['bool']);
    }

    /**
     * return all nulls inside vector
     * @return array
     */
    public function getNullsInside()
    {
        return $this->getFiltered(['null']);
    }

    /**
     * get chunked vector
     * @param $size
     * @param bool $preserveKeys
     * @return array
     */
    public function chunk($size, $preserveKeys = true)
    {
        return array_chunk($this->vector, $size, $preserveKeys);
    }

    public function countValues()
    {
        $counts = Vector::factory([]);
        foreach ($this->vector as $key => $value) {
            $counts->set($value, $this->keyByValue($value)->count());
        }
        return $counts;
    }

    public function sortDesc($keepKeys = true){
        if($keepKeys){
            arsort($this->vector);
        }else{
            rsort($this->vector);
        }
    }

    public function sortAsc($keepKeys = true){
        if($keepKeys){
            rsort($this->vector);
        }else{
            sort($this->vector);
        }
    }

    public function sortDescKeys(){
        krsort($this->vector);
    }

    public function sortAscKeys(){
        ksort($this->vector);
    }

    public function shuffle(){
        shuffle($this->vector);
    }

    /**
     * return an copy of original vector without values matchs with recursion
     * @return array|null
     * @throws \ReflectionException
     */
    public function getVectorAllWithoutRecursion()
    {
        return DeepClone::factory($this->getVectorAll())->copyRemoveRecursion();
    }

    /**
     * return an copy of original vector without values matchs with recursion and closures objects
     * @return array|null
     * @throws \ReflectionException
     */
    public function getVectorAllWithoutRecursionAndClosure()
    {
        return DeepClone::factory($this->getVectorAll())->copyRemoveRecursionAndClosures();
    }

}