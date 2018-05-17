<?php

namespace Winged\Rewrite;

/**
 * This object serves as the parameter for the Rewrite class.
 * Class Parameter
 * @package Winged\Rewrite
 */
class Parameter{
    public $name, $index;

    /**
     * Parameter constructor.
     * @param $name
     * @param bool $index
     */
    public function __construct($name, $index = false){
        $this->name = $name;
        $this->index = $index;
    }
}
