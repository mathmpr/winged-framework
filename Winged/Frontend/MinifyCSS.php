<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\File\File;
use Winged\Buffer\Buffer;

/**
 * render view files in project
 *
 * Class MinifyCSS
 *
 * @package Winged\Frontend
 */
class MinifyCSS extends MinifyMaster
{
    /**
     * MinifyCSS constructor.
     *
     * @param null $controller
     */
    public function __construct($controller = null)
    {
        $this->controller = $controller;
        parent::__construct();
    }
}