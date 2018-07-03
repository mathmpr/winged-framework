<?php

namespace Winged\Form\Components;

use Winged\Components\ComponentParser;

/**
 * Class ButtonComponent
 * @package Winged\Form\Components
 */
class ButtonComponent extends ComponentParser{

    /**
     * @param $args
     */
    public function parser($args){
        extract($args);
        /**
         * @var $property string
         * @var $class string
         * @var $obj Model
         * @var $inputOptions array
         * @var $elementOptions array
         * @var $isArray bool
         */
        $this->reset();
        if (method_exists($class, 'labels')) {
            $labels = $obj->labels();
            if (($text = array_key_exists_check($property, $labels)) !== false) {
                $this->DOM->query('button')[0]->text($text);
            }
        }
        $this->addOptions($this->DOM->query('html *')[0], $inputOptions);
    }

    public function reset(){
        $reset = \pQuery::parseStr($this->original->html());
        $this->DOM = $reset;
    }
}