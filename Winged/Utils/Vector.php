<?php

namespace Winged\Utils;

use Winged\Error\Error;

/**
 * Class Vector
 */
class Vector implements \ArrayAccess
{

    public $vector = [];
    private $hashs = [];
    private $objectOffsets = [];

    public static function factory($vector = [])
    {
        return new Vector($vector);
    }

    public function __construct($vector = [])
    {
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
            $this->hashs[] = spl_object_id($value);
        }

        if (is_object($offset)) {
            $_offset = spl_object_hash($offset);
            $this->objectOffsets[$_offset] = $offset;
            $offset = $_offset;
        }

        if (is_string($offset) || is_int($offset)) {
            $this->vector[$offset] = $value;
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
        if (is_object($offset)) {
            $offset = spl_object_hash($offset);
        }
        if (array_key_exists($offset, $this->vector)) {
            if (is_object($this->vector[$offset])) {
                $hash = spl_object_id($this->vector[$offset]);
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
            return in_array(spl_object_id($value), $this->hashs);
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
                if (!in_array(spl_object_id($vector), $stack)) {
                    $stack[] = spl_object_id($vector);
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
     * @param array | object $vector
     * @param array $stack
     * @return array|null
     * @throws \ReflectionException
     */
    private function removeRecursion($vector, &$stack = [])
    {
        if ($vector) {
            if (is_object($vector) && !in_array($vector, $stack, true) && !is_callable($vector)) {
                $stack[] = &$vector;
                if (get_class($vector) === 'stdClass') {
                    foreach ($vector as $key => $value) {
                        if (in_array($vector->{$key}, $stack, true)) {
                            unset($vector->{$key});
                            $vector->{$key} = null;
                        } else {
                            $vector->{$key} = $this->removeRecursion($vector->{$key}, $stack);
                        }
                    }
                    return $vector;
                } else {
                    $object = new \ReflectionObject($vector);
                    $reflection = new \ReflectionClass($vector);
                    $properties = $reflection->getProperties();
                    if ($properties) {
                        foreach ($properties as $property) {
                            $property = $object->getProperty($property->getName());
                            $property->setAccessible(true);
                            if (!is_callable($property->getValue($vector))) {
                                $private = false;
                                if ($property->isPrivate()) {
                                    $property->setAccessible(true);
                                    $private = true;
                                }

                                if (in_array($property->getValue($vector), $stack, true)) {
                                    $property->setValue($vector, null);
                                } else {
                                    $property->setValue($vector, $this->removeRecursion($property->getValue($vector), $stack));
                                }

                                if ($private) {
                                    $property->setAccessible(false);
                                }
                            }
                        }
                    }
                    return $vector;
                }
            } else if (is_array($vector)) {
                $nvector = [];
                foreach ($vector as $key => $value) {
                    $nvector[$key] = $this->removeRecursion($value, $stack);
                }
                return $nvector;
            } else {
                if (is_object($vector) && !is_callable($vector)) {
                    return null;
                }
            }
        }
        return $vector;
    }

    /**
     * return an copy of original vector without values matchs with recursion
     * @return array|null
     * @throws \ReflectionException
     */
    public function getVectorAllWithoutRecursion()
    {
        return $this->removeRecursion(DeepClone::factory($this->getVectorAll())->copy(), $stack);
    }

    /**
     * @param object|array $vector
     * @return array
     * @throws \ReflectionException
     */
    private function removeClosure($vector = [])
    {
        if ($vector) {
            if (is_object($vector)) {
                if (get_class($vector) === 'stdClass') {
                    foreach ($vector as $key => $value) {
                        if (is_callable($value)) {
                            $vector->{$key} = null;
                        } else {
                            if (is_object($value) || is_array($value)) {
                                $vector->{$key} = $this->removeClosure($value);
                            }
                        }
                    }
                } else {
                    $object = new \ReflectionObject($vector);
                    $reflection = new \ReflectionClass($vector);
                    $properties = $reflection->getProperties();
                    if ($properties) {
                        foreach ($properties as $property) {
                            $private = false;
                            $property = $object->getProperty($property->getName());
                            if ($property->isPrivate() || $property->isProtected()) {
                                $property->setAccessible(true);
                                $private = true;
                            }
                            $get = $property->getValue($vector);
                            if (is_callable($get)) {
                                $property->setValue($vector, null);
                            } else {
                                if (is_object($get) || is_array($get)) {
                                    $property->setValue($this->removeClosure($get), null);
                                }
                            }
                            if ($private) {
                                $property->setAccessible(false);
                            }
                        }
                    }
                }
            } else if (is_array($vector)) {
                foreach ($vector as $key => $value) {
                    if (is_callable($value)) {
                        $vector[$key] = null;
                    } else {
                        if (is_object($value) || is_array($value)) {
                            $vector[$key] = $this->removeClosure($value);
                        }
                    }
                }
            }
        }
        return $vector;
    }

    /**
     * return an copy of original vector without values matchs with recursion and closures objects
     * @return array|null
     * @throws \ReflectionException
     */
    public function getVectorAllWithoutRecursionAndClosure()
    {
        return $this->removeClosure($this->removeRecursion(DeepClone::factory($this->getVectorAll())->copy(), $stack));
    }

}