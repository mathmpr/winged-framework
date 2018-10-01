<?php

namespace Winged\Form\Components;

use Winged\Components\ComponentParser;
use Winged\Model\Model;

/**
 * Class TextareaComponent
 * @package Winged\Form\Components
 */
class TextareaComponent extends ComponentParser{

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
        $classEnd = explode('\\', $class);
        $classEnd = end($classEnd);
        $this->reset();
        if ($isArray) {
            $this->DOM->query('textarea')->attr('name', $class . '[' . $property . '][]');
        } else {
            $this->DOM->query('textarea')->attr('name', $class . '[' . $property . ']');
        }

        $this->DOM->query('textarea')->attr('id', $classEnd . '_' . $property);
        if (($value = array_key_exists('value', $inputOptions))) {
            $this->DOM->query('textarea')->text($value);
        } else {
            $this->DOM->query('textarea')->text($obj->requestKey($property));
        }
        $this->DOM->query('label:first-child')->attr('for', $classEnd . '_' . $property);

        if (method_exists($class, 'labels')) {
            $labels = $obj->labels();
            if (($text = array_key_exists_check($property, $labels)) !== false) {
                $this->DOM->query('label')[0]->text($text);
            }
        }

        $this->addOptions($this->DOM->query('textarea'), $inputOptions);
        $this->addOptions($this->DOM->query('html *')[0], $elementOptions);
    }

    public function reset(){
        $reset = \pQuery::parseStr($this->original->html());
        $this->DOM = $reset;
    }
}