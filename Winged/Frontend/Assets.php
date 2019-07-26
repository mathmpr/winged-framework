<?php

namespace Winged\Frontend;

use Winged\File\File;
use Winged\Error\Error;
use \WingedConfig;
use \Exception;

/**
 * Class Assets
 *
 * @package Winged\Frontend
 */
class Assets
{
    /**
     * @var array $js store path and options for js files
     */
    public $js = [];

    /**
     * @var array $css store path and options for css files
     */
    public $css = [];

    /**
     * @var array $appendedAbstractHeadContent store any content for append inside <head></head>
     */
    protected $appendedAbstractHeadContent = [];

    /**
     * @var array $htmlTagClasses store list of classes for insert into html tag
     */
    protected $htmlTagClasses = [];

    /**
     * @var array $bodyTagClasses store list of classes for insert into body tag
     */
    protected $bodyTagClasses = [];

    /**
     * @var null | string $bodyId store id property for body, if it is null, id equal controller / route name
     */
    protected $bodyId = null;

    /**
     * @var null | string $htmlId store id property for html, if it is null, id equal controller / route name
     */
    protected $htmlId = null;

    /**
     * @var $minifyJs MinifyMasterJS
     */
    private $minifyJs;

    /**
     * @var $minifyCss MinifyMasterCSS
     */
    private $minifyCss;

    /**
     * Assets constructor.
     */
    public function __construct()
    {
        $this->minifyJs = new MinifyJS($this);
        $this->minifyCss = new MinifyCSS($this);
    }

    /**
     * return js stack
     *
     * @return array
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * return css stack
     *
     * @return array
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * remove js from assets stack
     *
     * @param $identifier
     *
     * @return bool
     */
    public function removeJs($identifier)
    {
        if (array_key_exists($identifier, $this->js)) {
            unset($this->js[$identifier]);
            return true;
        }
        return false;
    }

    /**
     * remove css from assets stack
     *
     * @param $identifier
     *
     * @return bool
     */
    public function removeCss($identifier)
    {
        if (array_key_exists($identifier, $this->css)) {
            unset($this->css[$identifier]);
            return true;
        }
        return false;
    }

    /**
     * append class name in body tag
     *
     * @param null | string $className
     *
     * @return $this
     */
    public function appendBodyClass($className = null)
    {
        if ($className) {
            if (!in_array($className, $this->bodyTagClasses)) {
                $this->bodyTagClasses[] = $className;
            }
        }
        return $this;
    }

    /**
     * remove class name from body tag
     *
     * @param null | string $className
     *
     * @return $this
     */
    public function removeBodyClass($className = null)
    {
        if ($className) {
            if (in_array($className, $this->bodyTagClasses)) {
                unset($this->bodyTagClasses[array_search($className, $this->bodyTagClasses)]);
            }
        }
        return $this;
    }

    /**
     * ads or get body id
     *
     * @param null|string $id
     *
     * @return $this|string|null
     */
    public function bodyId($id = null)
    {
        if (!$id) return $this->bodyId;
        if (is_string($id)) {
            $this->bodyId = $id;
        }
        return $this;
    }

    /**
     * append class name in html tag
     *
     * @param null | string $className
     *
     * @return $this
     */
    public function appendHtmlClass($className = null)
    {
        if ($className) {
            if (!in_array($className, $this->htmlTagClasses)) {
                $this->htmlTagClasses[] = $className;
            }
        }
        return $this;
    }

    /**
     * remove class name from html tag
     *
     * @param null | string $className
     *
     * @return $this
     */
    public function removeHtmlClass($className = null)
    {
        if ($className) {
            if (in_array($className, $this->htmlTagClasses)) {
                unset($this->htmlTagClasses[array_search($className, $this->htmlTagClasses)]);
            }
        }
        return $this;
    }

    /**
     * ads or get html id
     *
     * @param null|string $id
     *
     * @return $this|string|null
     */
    public function htmlId($id = null)
    {
        if (!$id) return $this->htmlId;
        if (is_string($id)) {
            $this->htmlId = $id;
        }
        return $this;
    }

    /**
     * append an abstract head content from head content stack
     *
     * @param string $identifier
     * @param string $contentOrFilePath
     *
     * @return bool
     */
    public function appendAbstractHead($identifier, $contentOrFilePath)
    {
        $file = new File($contentOrFilePath, false);
        if ($file->exists()) {
            $this->appendedAbstractHeadContent[$identifier] = $file->read();
        }
        $this->appendedAbstractHeadContent[$identifier] = $contentOrFilePath;
        return true;
    }

    /**
     * remove an abstract head content from head content stack
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function removeAbstractHead($identifier)
    {
        if (array_key_exists($identifier, $this->appendedAbstractHeadContent)) {
            unset($this->appendedAbstractHeadContent[$identifier]);
            return true;
        }
        return false;
    }

    /**
     * append js path or content into assets stack
     *
     * @param       $identifier
     * @param       $contentOrFilePath
     * @param array $options
     * @param bool  $url
     *
     * @return bool
     */
    public function appendJs($identifier, $contentOrFilePath, $options = [], $url = false)
    {
        return $this->append('js', $identifier, $contentOrFilePath, $options, $url);
    }

    /**
     * append css path or content into assets stack
     *
     * @param string $identifier
     * @param string $contentOrFilePath
     * @param array  $options
     * @param bool   $url
     *
     * @return bool
     */
    public function appendCss($identifier, $contentOrFilePath, $options = [], $url = false)
    {
        return $this->append('css', $identifier, $contentOrFilePath, $options, $url);
    }

    /**
     *
     *
     * @param string $property
     * @param string $identifier
     * @param string $contentOrFilePath
     * @param array  $options
     * @param bool   $url
     *
     * @return bool
     */
    private function append($property, $identifier, $contentOrFilePath, $options = [], $url = false)
    {
        if (file_exists($contentOrFilePath) && !is_directory($contentOrFilePath) && !$url) {
            if (array_key_exists($identifier, $this->js)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, js file '" . $this->js[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->{$property}[$identifier] = [];
            $this->{$property}[$identifier]['string'] = $contentOrFilePath;
            $this->{$property}[$identifier]['type'] = 'file';
            $this->{$property}[$identifier]['options'] = $options;
            return true;
        } else if (!$url) {
            if (array_key_exists($identifier, $this->{$property})) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, script '" . htmlspecialchars($this->{$property}[$identifier]['string']) . "' never load.", __FILE__, __LINE__);
            }
            $this->{$property}[$identifier] = [];
            $this->{$property}[$identifier]['string'] = $contentOrFilePath;
            $this->{$property}[$identifier]['type'] = 'script';
            $this->{$property}[$identifier]['options'] = $options;
            return true;
        } else if ($url) {
            if (array_key_exists($identifier, $this->{$property})) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, script url '" . $this->{$property}[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->{$property}[$identifier] = [];
            $this->{$property}[$identifier]['string'] = $contentOrFilePath;
            $this->{$property}[$identifier]['type'] = 'url';
            $this->{$property}[$identifier]['options'] = $options;
            return true;
        }
        return false;
    }

    /**
     * active minify for JS and CSS files in Assets files stack
     *
     * @throws Exception
     */
    protected function activeMinify()
    {
        if (WingedConfig::$config->AUTO_MINIFY !== false) {
            $this->minifyCss->minify('css');
            $this->minifyJs->minify('js');
        }
    }

}