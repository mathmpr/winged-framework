<?php

namespace Winged\Utils;

/**
 * DeepClone
 */
class DeepClone
{

    private $target = false;

    public static function factory($clonable = null)
    {
        return new DeepClone($clonable);
    }

    public function __construct($clonable = null)
    {
        $this->setClonable($clonable);
    }

    public function setClonable($clonable = null)
    {
        if (is_object($clonable) || is_array($clonable)) {
            $this->target = $clonable;
        }
    }

    private function recursiveCopy($origin = null, $target = null, &$stack = [], &$objects = [])
    {
        if (!$target) {
            if (is_object($origin) && !in_array(spl_object_id($origin), $stack)) {
                if (get_class($origin) === 'stdClass') {
                    $target = new \stdClass();
                    $stack[spl_object_id($origin)] = spl_object_id($target);
                    $objects[spl_object_id($origin)] = &$origin;
                    $objects[spl_object_id($target)] = &$target;
                    foreach ($origin as $key => $value) {
                        if (is_object($value) || is_array($value)) {
                            if (is_object($value)) {
                                if (array_key_exists(spl_object_id($value), $stack)) {
                                    $target->{$key} = &$objects[$stack[spl_object_id($value)]];
                                } else {
                                    $target->{$key} = $this->recursiveCopy($value, null, $stack, $objects);
                                }
                            } else {
                                $target->{$key} = $this->recursiveCopy($value, null, $stack, $objects);
                            }
                        } else {
                            $target->{$key} = $value;
                        }
                    }
                    return $target;
                } else {
                    $reflection = (new \ReflectionClass(get_class($origin)));
                    $target = unserialize(
                        sprintf('O:%d:"%s":0:{}', strlen(get_class($origin)), get_class($origin))
                    );
                    $stack[spl_object_id($origin)] = spl_object_id($target);
                    $objects[spl_object_id($origin)] = &$origin;
                    $objects[spl_object_id($target)] = &$target;
                    $properties = $reflection->getProperties();
                    if ($properties) {
                        foreach ($properties as $property) {
                            $private = false;
                            if ($property->isProtected() || $property->isPrivate() || $property->isPublic()) {
                                if ($property->isPrivate() || $property->isProtected()) {
                                    $property->setAccessible(true);
                                    $private = true;
                                }
                                $get = $property->getValue($origin);
                                if (is_object($get) && !is_callable($get)) {
                                    $property->setValue($target, $this->recursiveCopy($get, null, $stack, $objects));
                                } else {
                                    if (is_callable($get)) {
                                        $property->setValue($target, $get->bindTo($target));
                                    } else {
                                        $property->setValue($target, $get);
                                    }
                                }
                                if ($private) {
                                    $property->setAccessible(false);
                                }
                            }
                        }
                    }
                    return $target;
                }
            } else if (is_array($origin)) {
                $target = [];
                foreach ($origin as $key => $value) {
                    if (is_object($value) || is_array($value)) {
                        $target[$key] = $this->recursiveCopy($value, null, $stack);
                    } else {
                        $target[$key] = $value;
                    }
                }
                return $target;
            } else {
                $target = $origin;
            }
            return $target;
        }
        return false;
    }

    /**
     * @param array|object $vector
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

    public function copyRemoveRecursionAndClosures()
    {
        if ($this->target) {
            $copy = $this->recursiveCopy($this->target);
            if ($copy) {
                $copy = $this->removeRecursion($copy, $stack);
                $this->removeClosure($copy);
                return $copy;
            }
        }
        return false;
    }

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
     * return an copy of target without recursion inside it
     * @return array|bool|null
     */
    public function copyRemoveRecursion()
    {
        if ($this->target) {
            $copy = $this->recursiveCopy($this->target);
            if ($copy) {
                return $this->removeRecursion($copy, $stack);
            }
        }
        return false;
    }


    public function copy()
    {
        if ($this->target) {
            return $this->recursiveCopy($this->target);
        }
        return false;
    }
}