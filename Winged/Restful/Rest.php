<?php 

namespace Winged\Restful;

/**
 * This object serves as the parameter for the Restful class.
 * Class Parameter
 * @package Winged\Rewrite
 */
class Rest{
	public $name, $method, $rule, $class_path, $class_name, $construct;

    /**
     * Rest constructor.
     * @param $name
     * @param bool $rule
     * @param bool $construct
     * @param bool $method
     * @param bool $class_path
     * @param bool $class_name
     */
	public function __construct($name, $rule = false, $construct = false, $method = false, $class_path = false, $class_name = false){
		$this->name = $name;
		$this->method = $method;
		$this->class_path = $class_path;
		$this->rule = $rule; 
		$this->class_name = $class_name;
		$this->construct = $construct;
	}	
}