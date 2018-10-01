<?php

namespace Winged\Form\Components;

use Winged\Components\ComponentParser;
use Winged\Model\Model;

/**
 * Class SelectComponent
 * @package Winged\Form\Components
 */
class SelectComponent extends ComponentParser{

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
        $classEnd = explode('\\', $class);
        $classEnd = end($classEnd);
        if ($isArray) {
            $this->DOM->query('select')->attr('name', $class . '[' . $property . '][]');
        } else {
            $this->DOM->query('select')->attr('name', $class . '[' . $property . ']');
        }
        $this->DOM->query('select')->attr('id', $classEnd . '_' . $property);
        $this->DOM->query('select')[0]->parent->parent->query('label')->attr('for', $classEnd . '_' . $property);

        if (method_exists($class, 'labels')) {
            $labels = $obj->labels();
            if (($text = array_key_exists_check($property, $labels)) !== false) {
                $this->DOM->query('label')[0]->text($text);
            }
        }

        if (($data = array_key_exists_check('options', $inputOptions))) {
            if (is_array($data) && !empty($data)) {
                foreach ($data as $key => $cla) {
                    $selected = '';
                    if ($key == $obj->{$property}) {
                        $selected = ' selected="selected"';
                    }
                    $this->DOM->query('select')->append('<option value="' . $key . '"' . $selected . '>' . $cla . '</option>');
                }
            }
        }

        $this->addOptions($this->DOM->query('select'), $inputOptions);
        $this->addOptions($this->DOM->query('html *')[0], $elementOptions);

    }

    public function reset(){
        $reset = \pQuery::parseStr($this->original->html());
        $this->DOM = $reset;
    }
}