<?php

use Winged\Controller\Controller;
use Winged\Image\Image;
use Winged\Model\News;
use Winged\Components\Components;
use Winged\Components\ComponentParser;
use Winged\File\Ftp;

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

        $ftp = new Ftp('pradoit-com-br.umbler.net', 'pradoit-com-br', 'QWEqwe123');

        //$ftp->up();

        //pre_clear_buffer_die($ftp);

        $ftp->down('public');

        $ftp->put('./cacau.png', './now/gety/images/cacau.png', true);

        pre_clear_buffer_die($ftp->path);

        //$ftp->rmdir('now');

        //$ftp->put('./cacau.png', './now/try/create/cacau.png', true);

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
        $image->printable();
    }

}