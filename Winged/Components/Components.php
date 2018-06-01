<?php

namespace Winged\Components;

use Winged\Directory\Directory;
use Winged\Error\Error;
use Winged\File\File;

/**
 * Class Component
 * @package Winged\Component
 */
class Components
{

    /**
     * @var array $components
     */
    public $components = [];
    /**
     * @var null|Directory
     */

    /**
     * Components constructor.
     * @param null|string $name
     * @param ComponentParser|null $component
     */
    public function __construct($name = null, ComponentParser $component = null)
    {
        $this->add($name, $component);
    }

    /**
     * @param null|string $name
     * @param ComponentParser|null $component
     */
    public function add($name = null, ComponentParser $component = null)
    {
        if(is_object($component) && get_parent_class($component) === 'Winged\Components\ComponentParser' && is_string($name)){
            $this->components[$name] = $component;
            $this->components[$name]->setComponentController($this);
            $this->components[$name]->includeCheck();
            if (is_array($this->components[$name]->properties)) {
                foreach ($this->components[$name]->properties as $key => $parameter) {
                    $this->{$key} = $parameter;
                    unset($this->components[$name]->properties[$key]);
                }
            }
        }
    }

    /**
     * @param $componentName
     * @return bool|mixed|ComponentParser
     */
    public function get($componentName){
        if(array_key_exists($componentName, $this->components)){
            return $this->components[$componentName];
        }
        return false;
    }

}