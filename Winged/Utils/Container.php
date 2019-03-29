<?php

namespace Winged\Utils;

/**
 * Class Container
 * @package Winged\Container
 */
class Container
{
    protected $target;
    protected $className;
    protected $methods = [];
    public $vars = [];

    /**
     * @var $self Container
     */
    public static $self = null;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function attach($name, $method)
    {
        if (!$this->className && !is_null($this->target)) {
            $this->className = get_class($this->target);
        }
        $binded = \Closure::bind($method, $this->target, $this->className);
        $this->methods[$name] = $binded;
    }

    public function __set($name = '', $value = false){
        if(is_string($name) && $name != ''){
            $this->vars[$name] = $value;
        }
    }

    public function __get($name){
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        return null;
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methods)) {
            return call_user_func_array($this->methods[$name], $arguments);
        }

        if (method_exists($this->target, $name)) {
            return call_user_func_array(
                array($this->target, $name),
                $arguments
            );
        }
    }

    public function methodExists($name)
    {
        if (array_key_exists($name, $this->methods)) {
            return true;
        }
    }

}