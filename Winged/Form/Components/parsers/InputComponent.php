<?php

namespace Winged\Form\Components;

use Winged\Components\ComponentParser;
use Winged\Model\Model;

/**
 * Class InputComponent
 * @package Winged\Form\Components
 */
class InputComponent extends ComponentParser
{

    /**
     * @param $args
     */
    public function parser($args)
    {
        extract($args);
        /**
         * @var $property string
         * @var $class string
         * @var $obj Model
         * @var $inputOptions array
         * @var $elementOpctions array
         * @var $isArray bool
         */
        $this->reset();
        if ($isArray) {
            $this->DOM->query('input')->attr('name', $class . '[' . $property . '][]');
        } else {
            $this->DOM->query('input')->attr('name', $class . '[' . $property . ']');
        }
        $this->DOM->query('input')->attr('id', $class . '_' . $property);
        $this->DOM->query('input')[0]->parent->parent->query('label')->attr('for', $class . '_' . $property);

        $this->DOM->query('input')->attr('id', $class . '_' . $property);
        $this->DOM->query('input')->attr('value', $obj->requestKey($property));
        $this->DOM->query('label:first-child')->attr('for', $class . '_' . $property);

        if (method_exists($class, 'labels')) {
            $labels = $obj->labels();
            if (($text = array_key_exists_check($property, $labels)) !== false) {
                $this->DOM->query('label')[0]->text($text);
            }
        }

        $this->addOptions($this->DOM->query('input'), $inputOptions);
        $this->addOptions($this->DOM->query('html *')[0], $inputOptions);
    }

    public function reset()
    {
        $reset = \pQuery::parseStr($this->original->html());
        $this->DOM = $reset;
    }
}