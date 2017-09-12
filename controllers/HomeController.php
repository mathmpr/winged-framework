<?php

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->assets->site();
    }

    public function actionIndex()
    {
        $this->renderHtml("home");
    }
}