<?php

namespace Winged\Form\Components;

use Winged\Components\ComponentParser;

/**
 * Class FormComponent
 * @package Winged\Form\Components
 */
class FormComponent extends ComponentParser
{

    /**
     * @param $action
     * @param $method
     * @param $options
     * @param $enctype
     */
    public function parser($action, $method, $options, $enctype)
    {
        $this->DOM->query('form')->attr('action', $action);
        $this->DOM->query('form')->attr('method', $method);
        $this->DOM->query('form')->attr('enctype', $enctype);
        $this->addOptions($this->DOM->query('form'), $options);
    }
}