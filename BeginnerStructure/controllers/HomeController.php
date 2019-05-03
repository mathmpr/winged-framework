<?php

use PagSeguro\Config;
use Winged\Controller\Controller;
use Winged\File\File;
use Winged\Form\Form;
use Winged\Formater\Formater;
use Winged\Http\Request;
use Winged\Image\Image;
use Winged\Utils\RandomName;
use Winged\Winged;

/**
 * Class HomeController
 */
class HomeController extends Controller
{
    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function actionIndex(){

        pre_clear_buffer_die('asdasdasd');
        //$test = new Test();

        $this->rewriteHeadContentPath('./head.php');
        $this->renderHtml('home');
    }

}