<?php

class HomeController extends Controller{

    function __construct()
    {
        parent::__construct();
    }

    function actionIndex(){
        $this->renderHtml('home');
    }

}