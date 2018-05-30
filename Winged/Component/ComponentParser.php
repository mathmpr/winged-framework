<?php

namespace Winged\Component;

use Masterminds\HTML5;
use Winged\File\File;

/**
 * Class ComponentParser
 * @package Winged\Component
 */

class ComponentParser
{
    /**
     * @var null|Component
     */
    public $component = null;

    public $properties = [];

    /**
     * @var null|\pQuery
     */
    public $DOM = null;

    /**
     * ComponentParser constructor.
     * @param File $template
     */
    function __construct(File $template)
    {
        $html5 = new HTML5();
        $this->DOM = \pQuery::parseStr($html5->saveHTML($html5->loadHTML($template->read())));
        $includes = $this->DOM->query('x-include');
        if($includes){
            /**
             * @var $include \pQuery
             */
            foreach ($includes as $include){
                if($include->attr('template')){
                    $component = new Component($include->attr('directory'));
                    pre_clear_buffer_die($component);
                    //$component->configure([
                    //    $include->attr('template')
                    //]);
                }
            }
        }
    }

    function setComponent($component){
        $this->component = $component;
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
     * @param $selector
     * @param null $text
     * @return bool|String
     */
    function text($selector, $text = null)
    {
        if (!is_null($selector)) {
            if (is_null($text)) {
                return $this->DOM->query($selector)->text();
            } else {
                $this->DOM->query($selector)->text($text);
            }
        }
        return false;
    }

    /**
     * @return string
     */
    function freeReturn()
    {
        return $this->DOM->html();
    }

    function free()
    {
        echo $this->DOM->html();
    }

}