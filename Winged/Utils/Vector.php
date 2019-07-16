<?php

namespace Winged\Utils;

/**
 * Class Vector
 */
class Vector implements \ArrayAccess, \Iterator
{
    /**
     * the "internal pointer" of a vector, can be get with $this->key()
     * @var int|null
     */
    private $position = null;
    private $vector = [];
    private $hashs = [];
    private $objectOffsets = [];
    private $arrayOffsets = [];
    /**
     * if during use of an vector you use unset inside an loop using next() method, the next call of next() cant use native next($this->vector) because
     * when unset() is used in a literal array, the internal pointer of this array changed to next position automatically
     * @var $unseted bool
     */
    private $unseted = false;

    /**
     * @param array $vector
     *
     * @return Vector
     */
    public static function factory($vector = [])
    {
        return new Vector($vector);
    }

    /**
     * Vector constructor.
     *
     * @param array $vector
     */
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
            if (is_array($offset)) {
                $offset = serialize($offset);
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
    public function offset($offset)
    {
        if ($this->keyExists($offset)) {

            if (is_object($offset)) {
                $offset = spl_object_hash($offset);
            }

            if (array_key_exists($offset, $this->objectOffsets)) {
                return $this->objectOffsets[$offset];
            }

            if (is_array($offset)) {
                $offset = serialize($offset);
            }

            if (array_key_exists($offset, $this->arrayOffsets)) {
                return $this->arrayOffsets[$offset];
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
        if (is_array($offset)) {
            $offset = serialize($offset);
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
     * return an value linked with offset if offset exists, but if value is an vector, this is cloned before return
     * @param bool $offset
     * @return array|bool|mixed|\stdClass|null
     */
    private function _get($offset = false)
    {
        $get = $this->offsetGet($offset);
        if (is_object($get) && get_class($get) === __CLASS__) {
            return DeepClone::factory($get)->copy();
        }
        return $get;
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
        if (is_array($offset)) {
            $_offset = serialize($offset);
            $this->arrayOffsets[$_offset] = $offset;
            $offset = $_offset;
        }

        if (is_scalar($offset)) {
            $this->vector[$offset] = $value;
        } else {
            if (!$offset) {
                $this->vector[] = $value;
            }
        }
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
            if (is_object($this->vector[$offset])) {
                $hash = spl_object_hash($this->vector[$offset]);
                $search = array_search($hash, $this->hashs, true);
                if ($search || is_int($search)) {
                    unset($this->hashs[$search]);
                }
            }
            unset($this->vector[$offset]);
            $this->unseted = true;
            if (array_key_exists($offset, $this->objectOffsets)) {
                unset($this->objectOffsets[$offset]);
            }
            if (array_key_exists($offset, $this->arrayOffsets)) {
                unset($this->arrayOffsets[$offset]);
            }
            return true;
        }
        return false;
    }

    /**
     * check if offset is valid
     * @return bool
     */
    function valid()
    {
        $pos = $this->offsetExists($this->position);
        if (!$pos) {
            $this->rewind();
        }
        return $pos;
    }

    /**
     * return current key of vector
     * @return int|mixed|string|null
     */
    function key()
    {
        return $this->offset(key($this->vector));
    }

    /**
     * go to next position in vector
     * @return bool|mixed
     */
    public function next()
    {
        if ($this->unseted) {
            $next = current($this->vector);
            $this->unseted = false;
        } else {
            $next = next($this->vector);
        }
        $this->position = key($this->vector);
        if ($next) {
            return $next;
        } else {
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
     * @param bool $search
     * @return Vector
     */
    public function keyByValue($value, $search = false)
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
        if ($search) {
            if (!empty($keys)) {
                foreach ($keys as $key => $value) {
                    if (array_key_exists($key, $this->objectOffsets)) {
                        $_keys[$this->objectOffsets[$key]] = $this->_get($key);
                    }
                    if (array_key_exists($key, $this->arrayOffsets)) {
                        $_keys[$this->arrayOffsets[$key]] = $this->_get($key);
                    } else {
                        $_keys[$key] = $this->_get($key);
                    }
                }
            }
        } else {
            if (!empty($keys)) {
                foreach ($keys as $key => $value) {
                    if (array_key_exists($key, $this->objectOffsets)) {
                        $_keys[] = $this->objectOffsets[$key];
                    } else if (array_key_exists($key, $this->arrayOffsets)) {
                        $_keys[] = $this->arrayOffsets[$key];
                    } else {
                        $_keys[] = $key;
                    }
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
                $get = $this->_get($offest);
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
        return !($this->count() > 0);
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
                if (array_key_exists($from, $this->arrayOffsets)) {
                    $objectKey = $this->arrayOffsets[$from];
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
        if (array_key_exists($from, $this->arrayOffsets)) {
            $objectKey = $this->arrayOffsets[$from];
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
     * @return $this
     */
    public function walkDeep($function = null)
    {
        if (is_callable($function)) {
            if (is_callable($function)) {
                $this->vector = $this->_walk($function, $this->vector);
            }
        }
        return $this;
    }

    /**
     * execute function on elements inside vector and if function return an value, the vector in position replace with it
     * @param callable $function
     * @throws \ReflectionException
     * @return $this
     */
    public function walk($function = null)
    {
        if (is_callable($function)) {
            $this->vector = $this->_walkOne($function, $this->vector);
        }
        return $this;
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
     * @return $this
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
        return $this;
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
                    $removed[$this->offset($key)] = $this->_get($key);
                    $this->offsetUnset($key);
                    $count++;
                }

                if ($count === $deleteCount) {
                    $remove = false;
                    if ($value) {
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
     * @param array|Vector $value
     * @param mixed $key
     * @return mixed
     */
    private static function abstractGet($value, $key)
    {
        if (is_object($value)) {
            if (get_class($value) === __CLASS__) {
                return $value->_get($key);
            }
            return $value[$key];
        } else {
            return $value[$key];
        }
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
                $array[] = self::abstractGet($value, $columnKey);
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar(self::abstractGet($value, $indexKey))) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = self::abstractGet($value, $columnKey);;
            }
        }
        return $array;
    }

    /**
     * get parts of vector with matched types
     * @param array $types
     * @param bool $cloneObjects
     * @return Vector
     */
    public function getFiltered($types = [], $cloneObjects = false)
    {
        $parts = Vector::factory([]);
        foreach ($this->vector as $key => $value) {
            if (is_scalar($value) && in_array('scalar', $types)) {
                $parts[$this->offset($key)] = $value;
                continue;
            }
            if (is_array($value) && in_array('array', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_resource($value) && in_array('resource', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_object($value) && in_array('object', $types)) {
                if (get_class($value) === __CLASS__ || $cloneObjects) {
                    $parts[$this->offset($key)] = DeepClone::factory($value)->copy();
                } else {
                    $parts[$this->offset($key)] = $value;
                }
            }
            if (is_int($value) && in_array('int', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_float($value) && in_array('float', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_double($value) && in_array('double', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_string($value) && in_array('string', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_bool($value) && in_array('bool', $types)) {
                $parts[$this->offset($key)] = $value;
            }
            if (is_null($value) && in_array('null', $types)) {
                $parts[$this->offset($key)] = $value;
            }
        }
        return $parts;
    }

    /**
     * return all arrays inside vector
     * @return Vector
     */
    public function getArraysInside()
    {
        return $this->getFiltered(['array']);
    }

    /**
     * return all resources inside vector
     * @return Vector
     */
    public function getResourcesInside()
    {
        return $this->getFiltered(['resource']);
    }

    /**
     * return all objects inside vector
     * @param bool $cloneObjects
     * @return Vector
     */
    public function getObjectsInside($cloneObjects = false)
    {
        return $this->getFiltered(['object'], $cloneObjects);
    }

    /**
     * return all integers inside vector
     * @return Vector
     */
    public function getIntegersInside()
    {
        return $this->getFiltered(['int']);
    }

    /**
     * return all floats inside vector
     * @return Vector
     */
    public function getFloatsInside()
    {
        return $this->getFiltered(['float']);
    }

    /**
     * return all doubles inside vector
     * @return Vector
     */
    public function getDoublesInside()
    {
        return $this->getFiltered(['double']);
    }

    /**
     * return all strings inside vector
     * @return Vector
     */
    public function getStringsInside()
    {
        return $this->getFiltered(['string']);
    }

    /**
     * return all booleans inside vector
     * @return Vector
     */
    public function getBooleansInside()
    {
        return $this->getFiltered(['bool']);
    }

    /**
     * return all nulls inside vector
     * @return Vector
     */
    public function getNullsInside()
    {
        return $this->getFiltered(['null']);
    }

    /**
     * return all scalar inside vector
     * @return Vector
     */
    public function getScalarsInside()
    {
        return $this->getFiltered(['scalar']);
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

    /**
     * search all values exact matched with value pass to this method and return new vector with founded values
     * @param $value
     * @return Vector
     */
    public function search($value)
    {
        return $this->keyByValue($value, true);
    }

    /**
     * search value in all string elements inside vector and if found put element inside new vector e return it
     * @param $value
     * @return Vector
     */
    public function searchStripos($value)
    {
        $founds = Vector::factory([]);
        /**
         * @var $strings Vector
         */
        $strings = $this->getStringsInside();
        foreach ($strings as $key => $string) {
            if (is_int(stripos($string, $value))) {
                $founds[$strings->offset($key)] = $string;
            }
        }
        return $founds;
    }

    /**
     * search value in all string elements inside vector and if found put element inside new vector e return it
     * @param $value
     * @return Vector
     */
    public function searchStrpos($value)
    {
        $founds = Vector::factory([]);
        /**
         * @var $strings Vector
         */
        $strings = $this->getStringsInside();
        foreach ($strings as $key => $string) {
            if (is_int(strpos($string, $value))) {
                $founds[$strings->offset($key)] = $string;
            }
        }
        return $founds;
    }

    /**
     * search value in all string elements inside vector and if found put element inside new vector e return it
     * @param $pattern
     * @return Vector
     */
    public function searchPregMatch($pattern)
    {
        $founds = Vector::factory([]);
        /**
         * @var $strings Vector
         */
        $strings = $this->getStringsInside();
        foreach ($strings as $key => $string) {
            preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE);
            if (!empty($matches)) {
                $founds[$strings->offset($key)] = $string;
            }
        }
        return $founds;
    }

    /**
     * return count values where value is an key and value is an int
     * @return Vector
     */
    public function countValues()
    {
        $counts = Vector::factory([]);
        foreach ($this->vector as $key => $value) {
            $counts->set($value, $this->keyByValue($value)->count());
        }
        return $counts;
    }

    /**
     * @param bool $keepKeys
     * @return $this
     */
    public function sortDesc($keepKeys = true)
    {
        if ($keepKeys) {
            arsort($this->vector);
        } else {
            rsort($this->vector);
        }
        return $this;
    }

    /**
     * @param bool $keepKeys
     * @return $this
     */
    public function sortAsc($keepKeys = true)
    {
        if ($keepKeys) {
            rsort($this->vector);
        } else {
            sort($this->vector);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function sortDescKeys()
    {
        krsort($this->vector);
        return $this;
    }

    /**
     * @return $this
     */
    public function sortAscKeys()
    {
        ksort($this->vector);
        return $this;
    }

    /**
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->vector);
        return $this;
    }

    /**
     * @return $this
     */
    public function flip()
    {
        $fliped = Vector::factory([]);
        foreach ($this as $key => $value) {
            $fliped[$value] = $this->offset($key);
            unset($this[$key]);
        }
        foreach ($fliped as $key => $value) {
            $this[$key] = $value;
        }
        unset($fliped);
        return $this;
    }

    public function join($glue = ''){
        return Chord::factory(join($glue, $this->getStringsInside()->getVector()));
    }

    public function unsetEmptyOffsets(){
        foreach ($this as $key => $value){
            if(empty($value)){
                unset($this[$key]);
            }
        }
        return $this;
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

/**
 * check if value is Traversable object and contain ArrayAccess
 * works on Vector objects and literal arrays
 * @param $value
 * @return bool
 */
function is_vector_or_array($value)
{
    if (is_array($value)) return true;
    if (is_object($value) && get_class($value) === 'Winged\Utils\Vector') return true;
    return false;
}