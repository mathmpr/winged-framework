<?php

use Winged\Controller\Controller;

/**
 * Class HomeController
 */
class HomeController extends Controller{

    public function actionIndex(){
        $this->html('./home');
    }

}