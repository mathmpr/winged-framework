<?php

namespace Winged\Component;


use Winged\External\PhpQuery\phpQuery;
use Winged\External\PhpQuery\phpQueryObject;
use Winged\File\File;

/**
 * Class ComponentParser
 * @package Winged\Component
 */
class ComponentParser
{

    public $properties = [];

    public $DOM = null;

    /**
     * ComponentParser constructor.
     * @param File $template
     */
    function __construct(File $template)
    {
        $this->DOM = phpQuery::newDocument($template->read());
    }

    /**
     * @param $property
     * @param null $value
     */
    function addProperty($property, $value = null)
    {
        $this->properties[$property] = $value;
    }

    /**
     * @param null | string $selector
     * @param null $text
     * @return String
     */
    function text($selector, $text = null){
        if(!is_null($selector)){
            if(is_null($text)){
                return $this->DOM->find($selector)->text();
            }else{
                $this->DOM->find($selector)->text($text);
            }
        }
        return false;
    }

    /**
     * @return string
     */
    function free(){
        return $this->DOM->markup();
    }

}