<?php

namespace Winged\Form;

use Winged\Winged;

class ActiveFormPrintable extends HtmlHelper
{
    public $class;
    public $refers = [];
    public $dimensions = [];

    /** @var $_html phpQueryObject */
    public $_html = null;

    /** @var $obj Model */
    public $obj = null;

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

    public function beggin($action = false, $method = 'get', $options = [], $enctype = 'multipart/form-data', $return = false)
    {
        if (!$action) {
            $action = Winged::$page_surname;
        }

        $html = $this->completeAnyHtmlPrintable('<form action="' . $action . '" method="' . $method . '" enctype="' . $enctype . '">', $options);

        if ($return) {
            return $html;
        }
        echo $html;
    }

    public function end()
    {
        echo '</form>';
    }

    public function addAnyHtml($html = '')
    {
        if ($this->_html != null) {
            $this->_html->find('form')->append($html);
        }
        return $this;
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