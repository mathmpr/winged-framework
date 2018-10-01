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

    public $printable = false;

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
        if(!$property){
            $property = '___null___';
        }
        if ((property_exists($this->class, $property) || $property === '___null___') && array_key_exists(mb_ucfirst($type), $this->components->components)) {
            $this->components->get(mb_ucfirst($type))->parser([
                'property' => $property,
                'class' => $this->class,
                'obj' => &$this->obj,
                'inputOptions' => $inputOptions,
                'elementOptions' => $elementOptions,
                'isArray' => $isArray
            ]);
            if ($forceReturn || $this->printable) {
                $this->applyErrorDistinct($property, $this->components->get(mb_ucfirst($type))->DOM->query('label.error'));
                return $this->components->get(mb_ucfirst($type))->DOM->html();
            } else {
                $this->appendHtml($this->components->get(mb_ucfirst($type))->DOM->html());
            }
        }
        return true;
    }

    /**
     * @param $property
     * @param $component \pQuery
     */
    private function applyErrorDistinct($property, $component){
        if(property_exists($this->class, $property)){
            if($this->obj->hasErrors()){
                $errors = $this->obj->getErrors();
                if(array_key_exists($property, $errors)){
                    $classEnd = explode('\\', $this->class);
                    $classEnd = end($classEnd);
                    $component->attr('style', 'display: block;');
                    $component->attr('for', $classEnd . '_' . $property);
                    $component->attr('id', $classEnd . '_' . $property . '_error');
                    $keys = array_keys($errors[$property]);
                    $component->text($errors[$property][$keys[0]]);
                }
            }
        }
    }

    /**
     * @param bool $action
     * @param string $method
     * @param array $options
     * @param string $enctype
     * @param bool $printable
     */
    public function begin($action = false, $method = 'get', $options = [], $enctype = 'multipart/form-data', $printable = false)
    {
        $this->printable = $printable;
        if (!$action) {
            $action = Winged::$page_surname;
        }
        return $this->components->get('Form')->parser($action, $method, $options, $enctype, $printable);
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
        if($this->printable){
            echo '<form>';
            return;
        }
        $this->applyError();
        echo $this->components->get('Form')->DOM->html();
    }

    private function applyError()
    {
        if ($this->obj->hasErrors()) {
            foreach ($this->obj->getErrors() as $property => $error) {
                $classEnd = explode('\\', $this->class);
                $classEnd = end($classEnd);
                $this->components->get('Form')->DOM->query('#' . $classEnd . '_' . $property)->query('label.error')->attr('style', 'display: block;');
                $this->components->get('Form')->DOM->query('#' . $classEnd . '_' . $property)->query('label.error')->attr('for', $classEnd . '_' . $property);
                $this->components->get('Form')->DOM->query('#' . $classEnd . '_' . $property)->query('label.error')->attr('id', $classEnd . '_' . $property . '_error');
                $this->components->get('Form')->DOM->query('#' . $classEnd . '_' . $property)->query('label.error')->text($error);
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