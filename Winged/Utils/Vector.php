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
        if (is_object($offset)) {
            $offset = spl_object_hash($offset);
        }
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
            if ($this->keyByValue($value)) {
                return false;
            }
            $this->hashs[] = spl_object_hash($value);
        }

        if (is_object($offset)) {
            $_offset = spl_object_hash($offset);
            $this->objectOffsets[$_offset] = $offset;
            $offset = $_offset;
        }

        if (is_string($offset) || is_int($offset)) {
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
        if (is_object($offset)) {
            $offset = spl_object_hash($offset);
        }
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
     * @return false|int|mixed|string|object
     */
    public function keyByValue($value)
    {
        $key = array_search($value, $this->vector, true);
        if ($key || is_int($key)) {
            if (array_key_exists($key, $this->objectOffsets)) {
                return $this->objectOffsets[$key];
            } else {
                return $key;
            }
        }
        return $key;
    }

    /**
     * return all keys linked with values passed
     * value inside values can be an object
     * @param array $values
     * @return array
     */
    public function keyByValueAll($values = [])
    {
        $keys = [];
        if (!is_array($values)) {
            if (is_object($values) && get_class($values) === 'stdClass') {
                $values = (array)DeepClone::factory($values)->copy();
            }
        }
        if (is_array($values)) {
            foreach ($values as $value) {
                $key = $this->keyByValue($value);
                if ($key || is_int($key)) {
                    $keys[] = $key;
                }
            }
        }
        return $keys;
    }

    /**
     * unset vector by value passed
     * value can be an object
     * @param $value
     * @return bool
     */
    public function unsetByValue($value)
    {
        $key = $this->keyByValue($value);
        if ($key || is_int($key)) {
            return $this->offsetUnset($key);
        }
        return false;
    }

    /**
     * unset all offsets in vector by passed values
     * value inside values can be an object
     * @param $values array
     * @return bool
     */
    public function unsetByValueAll($values = [])
    {
        $unset = true;
        if (!is_array($values)) {
            if (is_object($values) && get_class($values) === 'stdClass') {
                $values = (array)DeepClone::factory($values)->copy();
            }
        }
        if (is_array($values)) {
            foreach ($values as $value) {
                if (!$this->unsetByValue($value)) {
                    $unset = false;
                }
            }
        }
        return $unset;
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
        $return = [];
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

    function valid()
    {
        return isset($this->vector[$this->position]);
    }

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

    public function current()
    {
        return current($this->vector);
    }

    function rewind()
    {
        reset($this->vector);
        $this->position = key($this->vector);
        return true;
    }

    public function end()
    {
        return end($this->vector);
    }

    public function isEmpty()
    {
        return !(count($this->vector) > 0);
    }

    /**
     * @param $function
     * @param array $stack
     * @param array|object $runIn
     * @param string $from
     * @param null|object $original
     * @return array|callable
     * @throws \ReflectionException
     */
    private function _walk($function, $runIn = [], &$stack = [], $from = '', $original = null)
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
                                $get = $this->_walk($function, $value, $stack, $key, $runIn);
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
                                        $get = $this->_walk($function, $get, $stack, $property->getName(), $runIn);
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
                            $get = $this->_walk($function, $value, $stack, $key, $runIn);
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

    public function walkDeep($function = null)
    {
        if (is_callable($function)) {
            if (is_callable($function)) {
                $this->vector = $this->_walk($function, $this->vector);
            }
        }
    }

    public function walk($function = null)
    {
        if (is_callable($function)) {
            $this->vector = $this->_walkOne($function, $this->vector);
        }
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