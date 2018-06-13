<?php

use Winged\Controller\Controller;
use Winged\Image\Image;
use Winged\Model\News;
use Winged\Components\Components;
use Winged\Components\ComponentParser;

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

    public function actionIndex()
    {

        $articles = new News();
        $articles = $articles->select()->from(['fg' => News::tableName()])->find();

        $component = new Components('Home', ComponentParser::getComponent('./views/components/', 'Site\Components\Home', [
            'articles' => $articles
        ]));

        $component->get('Home')->begin();

        $component->get('Home')->free();

    }

    public function actionMyImage()
    {
        $image = new Image('http://www.tempie.com.br/assets/images/cacau.png', false);

        pre_clear_buffer_die();


        $image = new Image('./try.jpg');
        $image->printable();
    }

}