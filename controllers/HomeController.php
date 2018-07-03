<?php

use Winged\Controller\Controller;
use Winged\Image\Image;
use Winged\Components\Components;
use Winged\Components\ComponentParser;
use Winged\File\Ftp;
use Winged\Form\Form;

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
        $usuarios = new Usuarios();
        $form = new Form($usuarios);
        $form->begin();

        $form->addInput('nome', 'radio', [
            'values' => [
                0 => 'ADM',
                1 => 'FUN',
            ]
        ]);

        $form->end();


        //$ftp = new Ftp('pradoit-com-br.umbler.net', 'pradoit-com-br', 'QWEqwe123');

        //$ftp->up();

        //pre_clear_buffer_die($ftp);

        //$ftp->access('public/admin/assets/css/core/files/');

        //$ftp->put('./cacau.png', './now/gety/images/cacau.png', true);

        //$ftp->rmdir('now');

        //pre_clear_buffer_die($ftp->currentDir);

        //$ftp->rmdir('now');

        //$ftp->put('./cacau.png', './now/try/create/cacau.png', true);

        //$articles = new News();
        //$articles = $articles->select()->from(['fg' => News::tableName()])->find();

        //$component = new Components('Home', ComponentParser::getComponent('./views/components/', 'Site\Components\Home', [
        //    'articles' => $articles
        //]));

        //$component->get('Home')->begin();

        //$component->get('Home')->free();

    }

    public function actionMyImage()
    {
        $image = new Image('http://www.tempie.com.br/assets/images/cacau.png', false);
        $image->printable();
    }

}