<?php

namespace Winged\Form;

class ActiveForm extends HtmlHelper
{

    public $class;
    public $refers = [];
    public $dimensions = [];

    /** @var $_html phpQueryObject */
    public $_html = null;

    /** @var $obj Model */
    public $obj = null;

    public function beggin($action = false, $method = 'get', $options = [], $enctype = 'multipart/form-data')
    {
        if (!$action) {
            $action = Winged::$page_surname;
        }
        $this->_html = phpQuery::newDocument('<form></form>');
        $this->_html->find('form')->attr('action', $action);
        $this->_html->find('form')->attr('method', $method);
        $this->_html->find('form')->attr('enctype', $enctype);
        $this->_html->find('form')->replaceWith($this->completeAnyHtml($this->_html->find('form')->clone(), $options));
    }

    public function end($echo = false)
    {
        if ($echo) {
            echo $this->_html->markup();
            return true;
        }
        return $this->_html->markup();
    }

    public function addAnyHtml($html = '')
    {
        if ($this->_html != null) {
            $this->_html->find('form')->append($html);
        }
        return $this;
    }

    public function __construct(&$class)
    {
        if (is_object($class)) {
            $this->class = get_class($class);
            $this->obj = $class;
        } else if (class_exists($class)) {
            $this->class = $class;
            $this->obj = new $class();
        }
    }

    public function jQuerySelector($property, $html_tag = 'input', $dimension = false)
    {
        if ($tag = array_key_exists_check($property, $this->refers)) {
            $html_tag = $tag;
        }
        if ($dim = array_key_exists_check($property, $this->dimensions)) {
            $dimension = $dim;
        }
        if (property_exists($this->class, $property)) {
            if ($dimension) {
                return '$(\'' . $html_tag . '[name^="' . $this->class . '[' . $property . '][]"]\')';
            }
            return '$(\'' . $html_tag . '[name^="' . $this->class . '[' . $property . ']"]\')';
        }
        return false;
    }

}