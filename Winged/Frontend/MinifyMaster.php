<?php

namespace Winged\Frontend;

use Winged\File\File;

/**
 * render view files in project
 *
 * Class MinifyMaster
 *
 * @package Winged\Frontend
 */
class MinifyMaster
{

    /**
     * @var null | Controller | Assets | Render $controller
     */
    public $controller = null;

    /**
     * @var null | File
     */
    public $minify_info = null;

    /**
     * MinifyMaster constructor.
     */
    public function __construct()
    {
        $this->createMinify();
    }

    /**
     * @param bool $content
     */
    protected function createMinify($content = false)
    {
        $minify = new File('./minify.json', false);
        if (!$minify->exists()) {
            $minify = new File('./minify.json');
        }
        if ($content) {
            $minify->write($content);
        }
        $this->minify_info = $minify;
    }

    /**
     * @return bool|string|File
     */
    protected function readMinify()
    {
        $this->createMinify();
        return $this->minify_info->read();
    }

}