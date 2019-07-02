<?php

namespace Winged\Frontend;

use Winged\File\File;
use Winged\Buffer\Buffer;

/**
 * render view files in project
 *
 * Class MinifyJS
 *
 * @package Winged\Frontend
 */
class MinifyJS extends MinifyMaster
{

    /**
     * MinifyJS constructor.
     *
     * @param null $controller
     */
    public function __construct($controller = null)
    {
        $this->controller = $controller;
        parent::__construct();
    }




}