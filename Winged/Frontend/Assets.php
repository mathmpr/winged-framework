<?php

namespace Winged\Frontend;

use Winged\File\File;
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
     * @var $js array store path and options for js files
     */
    public $js = [];

    /**
     * @var $css array store path and options for css files
     */
    public $css = [];

    /**
     * @var $appended_abstract_head_content array store any content for append inside <head></head>
     */
    private $appended_abstract_head_content = [];

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
    public function getJs(){
        return $this->js;
    }

    /**
     * return css stack
     *
     * @return array
     */
    public function getCss(){
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
            $this->appended_abstract_head_content[$identifier] = $file->read();
        }
        $this->appended_abstract_head_content[$identifier] = $contentOrFilePath;
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
        if (array_key_exists($identifier, $this->appended_abstract_head_content)) {
            unset($this->appended_abstract_head_content[$identifier]);
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
    protected function activeMinify(){
        if(WingedConfig::$config->AUTO_MINIFY !== false){
            $this->minifyCss->minify('css');
            $this->minifyJs->minify('js');
        }
    }

}