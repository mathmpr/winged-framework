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

    /**
     * Component constructor.
     * @param string $directory
     */
    public function __construct($directory)
    {
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
    }

    /**
     * @param $parsers array
     */
    public function configure($parsers)
    {
        foreach ($parsers as $class => $parserParameters) {
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
                    $this->parsersObjects[$preservedName] = new $class($template);
                    foreach ($parserParameters as $key => $parameter){
                        if(is_string($key)){
                            $this->parsersObjects[$preservedName]->addProperty($key, $parameter);
                        }
                    }
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