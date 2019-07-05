<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
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
        /**
         * @var $controller null | Controller | Assets | Render
         */
        $this->controller = $controller;
        parent::__construct();
    }

    public function activeMinify($path, $read)
    {

    }




}