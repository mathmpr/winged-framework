<?php

namespace Winged\Components;

use Masterminds\HTML5;
use pQuery\DomNode;
use pQuery\IQuery;
use Winged\Directory\Directory;
use Winged\Error\Error;
use Winged\File\File;

/**
 * Class ComponentParser
 * @package Winged\Component
 */
class ComponentParser
{
    public $properties = [];

    /**
     * @var null | Components
     */
    public $component = null;

    /**
     * @var null|Directory
     */
    public $directory = null;

    /**
     * @var null|File
     */
    public $parser = null;

    /**
     * @var null|File
     */
    public $template = null;

    /**
     * @var null|\pQuery|DomNode|IQuery
     */
    public $DOM = null;

    /**
     * @var null|\pQuery|DomNode|IQuery
     */
    public $original = null;

    public $finalDOM = null;

    /**
     * @param $directory
     * @param null $class
     * @param null $properties
     * @return mixed
     */
    public static function getComponent($directory, $class = null, $properties = null)
    {
        $directory = new Directory($directory, false);;
        if ($directory->exists()) {
            $parser = new Directory($directory->folder . 'parsers/', false);
            $templates = new Directory($directory->folder . 'templates/', false);
            if (!$parser->exists() || !$templates->exists()) {
                Error::_die('Parser directory not exists or Template directory not exists', 'null', __FILE__, __LINE__);
            }

            if ($class && is_string($class)) {
                if (is_int(stripos($class, '\\'))) {
                    $explodeNamespace = explode('\\', $class);
                    $templateName = end($explodeNamespace);
                    $parserName = end($explodeNamespace) . 'Component';
                } else {
                    $templateName = $class;
                    $parserName = $class . 'Component';
                }
                $template = new File($templates->folder . $templateName . '.tpl', false);
                $parser = new File($parser->folder . $parserName . '.php', false);
                if ($template->exists() && $parser->exists()) {
                    include_once $parser->file_path;
                    if (class_exists($class . 'Component')) {
                        $class = $class . 'Component';

                        /**
                         * @var $class null | ComponentParser
                         */

                        $class = new $class();

                        $class->directory = $directory;
                        $class->parser = $parser;
                        $class->template = $template;

                        if (is_array($properties)) {
                            foreach ($properties as $key => $parameter) {
                                if (is_string($key)) {
                                    $class->addProperty($key, $parameter);
                                }
                            }
                        }

                        $html5 = new HTML5();
                        $class->DOM = \pQuery::parseStr($html5->saveHTML($html5->loadHTML($class->template->read())));
                        $class->original = \pQuery::parseStr($html5->saveHTML($html5->loadHTML($class->template->read())));

                        return $class;

                    }
                }
            }
        } else {
            Error::_die('Component directory not exists.', 'null', __FILE__, __LINE__);
        }
        return false;
    }

    /**
     * @return null|\pQuery|DomNode|IQuery
     */
    public function getOriginalDOM()
    {
        return \pQuery::parseStr($this->original->html());
    }

    /**
     * @return null|\pQuery|DomNode|IQuery
     */
    public function getEmptyDOM()
    {
        return \pQuery::parseStr('');
    }


    /**
     * @return $this
     */
    public function includeCheck()
    {
        $includes = $this->DOM->query('x-include');
        if ($includes) {
            /**
             * @var $include \pQuery|DomNode|IQuery
             */
            foreach ($includes as $include) {
                if ($include->attr('template') && $include->attr('directory') && $include->attr('name')) {
                    $this->component->add($include->attr('name'), ComponentParser::getComponent($include->attr('directory'), $include->attr('template')));
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function render()
    {
        $includes = $this->DOM->query('x-include');
        if ($includes) {
            /**
             * @var $include \pQuery|DomNode|IQuery
             */
            foreach ($includes as $include) {
                if ($include->attr('template') && $include->attr('directory') && $include->attr('name')) {
                    $include->after($this->component->get($include->attr('name'))->DOM->html());
                    $include->delete();
                    $this->component->get($include->attr('name'))->DOM = $this->DOM;
                }
            }
        }
        return $this;
    }

    /**
     * @param Components $component
     */
    function setComponentController(Components $component)
    {
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

    function free()
    {
        echo $this->DOM->html();
    }

    /**
     * @param $html \pQuery|DomNode|IQuery
     * @param $options array | callable
     * @return mixed
     */
    public function addOptions($html, $options)
    {
        if (($type = array_key_exists_check('type', $options)) !== false) {
            $html->attr('type', $type);
        }

        if (($placeholder = array_key_exists_check('placeholder', $options)) !== false) {
            $html->attr('placeholder', $placeholder);
        }

        if (($disable = array_key_exists_check('disable', $options)) !== false) {
            $html->attr('disable', $disable);
        }

        if (($autocomplete = array_key_exists_check('autocomplete', $options)) !== false) {
            $html->attr('autocomplete', $autocomplete);
        }

        if (($value = array_key_exists_check('value', $options)) !== false) {
            $html->attr('value', $value);
        }

        if (($id = array_key_exists_check('id', $options)) !== false) {
            $html->attr('id', $id);
        }

        if (($attrs = array_key_exists_check('attrs', $options)) !== false) {
            if (is_array($attrs) && !empty($attrs)) {
                foreach ($attrs as $attr => $value) {
                    if ($value) {
                        $html->attr($attr, $value);
                    }
                }
            }
        }

        if (($class = array_key_exists_check('class', $options)) !== false) {
            if (is_array($class) && !empty($class)) {
                foreach ($class as $cla) {
                    $html->addClass($cla);
                }
            }
        }

        if (($data = array_key_exists_check('data', $options))) {
            if (is_array($data) && !empty($data)) {
                foreach ($data as $key => $cla) {
                    $html->attr($key, $cla);
                }
            }
        }

        if (($selectors = array_key_exists_check('selectors', $options))) {
            if (is_array($selectors)) {
                foreach ($selectors as $selector => $function) {
                    if (is_array($function)) {
                        $keys = array_keys($function);
                        $method = array_shift($keys);
                        if ($method === 0) {
                            if (method_exists($html, $function[$method])) {
                                $html->query($selector)->{$function[$method]}();
                            }
                        } else {
                            if (method_exists($html, $method)) {
                                $html->query($selector)->{$method}($function[$method]);
                            }
                        }
                        $this->addOptions($html->query($selector), $function);
                    }
                }
            }
        }
        return true;
    }
}