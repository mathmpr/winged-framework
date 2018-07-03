<?php

namespace Winged\Form;

use function Winged\Formater\mb_ucfirst;
use Winged\Model\Model;
use Winged\Winged;
use Winged\Components\ComponentParser;
use Winged\Components\Components;

/**
 * Class Form creates a form in memory
 * @package Winged\Form
 */
class Form
{
    /**
     * @var
     */
    public $class;

    /**
     * @var null|Components
     */
    private $components = null;

    /** @var $obj Model */
    public $obj = null;

    /**
     * Form constructor.
     * @param $class
     */
    public function __construct(&$class)
    {
        if (is_object($class)) {
            $this->class = get_class($class);
            $this->obj = $class;
        } else if (class_exists($class)) {
            $this->class = $class;
            $this->obj = new $class();
        }
        $components = new Components();
        $components->add('Form', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Form'));
        $components->add('Input', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Input'));
        $components->add('Select', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Select'));
        $components->add('Button', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Button'));
        $components->add('Boolui', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\BoolUi'));
        $components->add('Checkbox', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Checkbox'));
        $components->add('Radio', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Radio'));
        $components->add('Textarea', ComponentParser::getComponent('./Winged/Form/Components/', 'Winged\Form\Components\Textarea'));
        $this->components = $components;
    }

    /**
     * @param $property
     * @param $type
     * @param array $inputOptions
     * @param array $elementOptions
     * @param bool $isArray
     * @param bool $forceReturn
     * @return bool|string
     */
    public function addInput($property, $type, $inputOptions = [], $elementOptions = [], $isArray = false, $forceReturn = false)
    {
        if (property_exists($this->class, $property) && array_key_exists(mb_ucfirst($type), $this->components->components)) {
            $this->components->get(mb_ucfirst($type))->parser([
                'property' => $property,
                'class' => $this->class,
                'obj' => &$this->obj,
                'inputOptions' => $inputOptions,
                'elementOpctions' => $elementOptions,
                'isArray' => $isArray
            ]);
            if ($forceReturn) {
                return $this->components->get(mb_ucfirst($type))->DOM->html();
            } else {
                $this->appendHtml($this->components->get(mb_ucfirst($type))->DOM->html());
            }
        }
        return true;
    }

    /**
     * @param bool $action
     * @param string $method
     * @param array $options
     * @param string $enctype
     */
    public function begin($action = false, $method = 'get', $options = [], $enctype = 'multipart/form-data')
    {
        if (!$action) {
            $action = Winged::$page_surname;
        }
        $this->components->get('Form')->parser($action, $method, $options, $enctype);
    }

    /**
     * Return form
     * @return \pQuery|\DomNode|bool|Form
     */
    public function endReturn()
    {
        $this->applyError();
        return $this->components->get('Form')->DOM->html();
    }

    /**
     * Free form
     */
    public function end()
    {
        $this->applyError();
        echo $this->components->get('Form')->DOM->html();
    }

    private function applyError()
    {
        if ($this->obj->hasErrors()) {
            foreach ($this->obj->getErros() as $property => $error) {
                $this->components->get('Form')->DOM->query('#' . $this->class . '_' . $property)->query('label.error')->attr('style', 'display: block;');
                $this->components->get('Form')->DOM->query('#' . $this->class . '_' . $property)->query('label.error')->attr('for', $this->class . '_' . $property);
                $this->components->get('Form')->DOM->query('#' . $this->class . '_' . $property)->query('label.error')->attr('id', $this->class . '_' . $property . '_error');
                $this->components->get('Form')->DOM->query('#' . $this->class . '_' . $property)->query('label.error')->text($error);
            }
        }
    }

    /**
     * Append content inside form
     * @param string $html
     * @return $this
     */
    public function appendHtml($html = '')
    {
        if ($this->components->get('Form')) {
            $this->components->get('Form')->DOM->query('form')->append($html);
        }
        return $this;
    }
}