<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\Winged;

/**
 * Class Assets
 *
 * @package Winged\Assets
 */
class Assets
{
    /**
     * @var $controller Controller
     */
    private $controller = null;

    /**
     * @var $js array store path and options for js files
     */
    public $js = [];

    /**
     * @var $css array store path and options for css files
     */
    public $css = [];

    /**
     * @var $remove_css array store path and options for css files
     */
    private $remove_css = [];

    /**
     * @var $remove_js array store path and options for css files
     */
    private $remove_js = [];

    /**
     * @var $head_path null | string store real path to file with head content
     */
    private $head_path = null;

    /**
     * @var $appended_abstract_head_content array store any content for append inside <head></head>
     */
    public $appended_abstract_head_content = [];
    /**
     * @var $minify_cache null | File
     */
    public $minify_cache = null;

    /**
     * @var $minifyJs MinifyJS
     */
    public $minifyJs;

    /**
     * @var $minifyCss MinifyCSS
     */
    public $minifyCss;

    /**
     * Assets constructor.
     *
     * @param Controller|null $controller
     */
    public function __construct(Controller $controller = null)
    {
        $this->minifyJs = new MinifyJS();
        $this->minifyCss = new MinifyCSS();
        $this->controller = $controller;
    }

    public function removeJs($identifier)
    {
        array_push($this->remove_js, $identifier);
        return;
    }

    public function removeCss($identifier)
    {
        array_push($this->remove_css, $identifier);
        return;
    }

    public function addJs($identifier, $string, $options = [], $url = false)
    {
        if (file_exists($string) && !is_directory($string) && !$url) {
            if (array_key_exists($identifier, $this->js)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, js file '" . $this->js[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->js[$identifier] = [];
            $this->js[$identifier]['string'] = $string;
            $this->js[$identifier]['type'] = 'file';
            $this->js[$identifier]['options'] = $options;
        } else if (!$url) {
            if (array_key_exists($identifier, $this->js)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, script '" . htmlspecialchars($this->js[$identifier]['string']) . "' never load.", __FILE__, __LINE__);
            }
            $this->js[$identifier] = [];
            $this->js[$identifier]['string'] = $string;
            $this->js[$identifier]['type'] = 'script';
            $this->js[$identifier]['options'] = $options;
        } else if ($url) {
            if (array_key_exists($identifier, $this->js)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, script url '" . $this->js[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->js[$identifier] = [];
            $this->js[$identifier]['string'] = $string;
            $this->js[$identifier]['type'] = 'url';
            $this->js[$identifier]['options'] = $options;
        }
        return;
    }

    public function addCss($identifier, $string, $options = [], $url = false)
    {
        if (file_exists($string) && !is_directory($string) && !$url) {
            if (array_key_exists($identifier, $this->css)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, script file '" . $this->css[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->css[$identifier] = [];
            $this->css[$identifier]['string'] = $string;
            $this->css[$identifier]['type'] = 'file';
            $this->css[$identifier]['options'] = $options;
        } else if (!$url) {
            if (array_key_exists($identifier, $this->css)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, css '" . htmlspecialchars($this->css[$identifier]['string']) . "' never load.", __FILE__, __LINE__);
            }
            $this->css[$identifier] = [];
            $this->css[$identifier]['string'] = $string;
            $this->css[$identifier]['type'] = 'script';
            $this->css[$identifier]['options'] = $options;
        } else if ($url) {
            if (array_key_exists($identifier, $this->css)) {
                Error::push(__CLASS__, "Index '" . $identifier . "' was doubled, css url '" . $this->css[$identifier]['string'] . "' never load.", __FILE__, __LINE__);
            }
            $this->css[$identifier] = [];
            $this->css[$identifier]['string'] = $string;
            $this->css[$identifier]['type'] = 'url';
            $this->css[$identifier]['options'] = $options;
        }
        return;
    }

    public function pushAbstractHead($identifier, $string)
    {
        $this->appended_abstract_head_content[$identifier] = $string;
    }

    public function removeAbstractHead($identifier)
    {
        if (array_key_exists($identifier, $this->appended_abstract_head_content)) {
            unset($this->appended_abstract_head_content[$identifier]);
        }
    }

}