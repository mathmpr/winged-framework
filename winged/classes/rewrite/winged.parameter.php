<?php 
class Parameter{
	public $name, $index;
	public function __construct($name, $index = false){
		$this->name = $name;
		$this->index = $index;
	}	
}

class Rest{
	public $name, $method, $rule, $class_path, $class_name, $construct;
	public function __construct($name, $rule = false, $construct = false, $method = false, $class_path = false, $class_name = false){
		$this->name = $name;
		$this->method = $method;
		$this->class_path = $class_path;
		$this->rule = $rule; 
		$this->class_name = $class_name;
		$this->construct = $construct;
	}	
}