<?php

class Assets
{
    /**
     * @var $controller Controller
     */
    private $controller = null;

    public function __construct(Controller $controller = null)
    {
        $this->controller = $controller;
    }

    public function site()
    {
        $this->controller->rewriteHeadContentPath('./head.php');
        $this->controller->addCss('reset', './winged/assets/css/reset.css');
        $this->controller->addCss('style', './winged/assets/css/install.css');
    }
}