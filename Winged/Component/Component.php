<?php

namespace Winged\Component;

use Winged\Directory\Directory;
use Winged\Error\Error;
use Winged\External\phpQuery;
use Winged\File\File;

/**
 * Class Component
 * @package Winged\Component
 */
class Component
{
    /**
     * @var array
     */
    public $parsersObjects = [];
    /**
     * @var null|Directory
     */
    public $directory = null;
    /**
     * @var null|Directory
     */
    public $parsers = null;
    /**
     * @var null|Directory
     */
    public $templates = null;

    public $registers = [];

    /**
     * Component constructor.
     * @param string $directory
     * @param false|array $startComponent
     */
    public function __construct($directory, $startComponent = null)
    {
        $this->construct($directory, $startComponent);
    }

    /**
     * Component constructor.
     * @param string $directory
     * @param false|array $startComponent
     */
    public function construct($directory, $startComponent = null){
        $directory = new Directory($directory, false);
        if ($directory->exists()) {
            $parser = new Directory($directory->folder . 'parsers/', false);
            $templates = new Directory($directory->folder . 'templates/', false);
            if (!$parser->exists() || !$templates->exists()) {
                Error::_die('Parser directory not exists or Template directory not exists', 'null', __FILE__, __LINE__);
            }
            $this->directory = $directory;
            $this->parsers = $parser;
            $this->templates = $templates;
        } else {
            Error::_die('Component directory not exists.', 'null', __FILE__, __LINE__);
        }
        if($startComponent){
            if(is_array($startComponent)){
                $this->startComponents($startComponent);
            }
        }
    }

    public function registerComponents($parsers){
        if(is_array($parsers)){
            $this->registers = array_merge($this->registers, $parsers);
        }
    }

    /**
     * @param $parsers array
     */
    public function startComponents($parsers)
    {
        foreach ($parsers as $class => $parserParameters) {
            if(is_int($class) && is_string($parserParameters)){
                $class = $parserParameters;
                $parserParameters = false;
            }

            if (is_int(stripos($class, '\\'))) {
                $explodeNamespace = explode('\\', $class);
                $templateName = end($explodeNamespace);
                $parserName = end($explodeNamespace) . 'Component';
                $preservedName = end($explodeNamespace);
            } else {
                $templateName = $class;
                $preservedName = $class;
                $parserName = $class . 'Component';
            }

            $template = new File($this->templates->folder . $templateName . '.tpl', false);
            $parser = new File($this->parsers->folder . $parserName . '.php', false);

            if ($template->exists() && $parser->exists()) {
                include_once $parser->file_path;
                if (class_exists($class . 'Component')) {
                    $class = $class . 'Component';
                    $parsedObject = new $class($template);
                    $this->parsersObjects[$preservedName] = new $class($template);
                    if(is_array($parserParameters)){
                        foreach ($parserParameters as $key => $parameter){
                            if(is_string($key)){
                                $parsedObject->addProperty($key, $parameter);
                            }
                        }
                    }
                    $parsedObject->setComponent($this);
                    $this->parsersObjects[$preservedName] = $parsedObject;
                } else {
                    Error::_die('Class ' . $class . 'Component' . ' not exists in ' . $parser->file_path . '', 'null', __FILE__, __LINE__);
                }
            } else {
                Error::_die('Parser file ' . $parserName . '.php not exists or Template file ' . $templateName . '.tpl not exists', 'null', __FILE__, __LINE__);
            }
        }
    }

    /**
     * @param $componentName
     * @return bool|mixed|ComponentParser
     */
    public function get($componentName){
        if(array_key_exists($componentName, $this->parsersObjects)){
            return $this->parsersObjects[$componentName];
        }
        return false;
    }

}