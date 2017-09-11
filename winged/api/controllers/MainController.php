<?php

class MainController extends Controller{

    public function __construct()
    {
        parent::__construct();
    }

    public function actionIndex(){
        $this->renderHtml('main');
    }

}
