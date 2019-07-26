<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Http\Session;
use Winged\Http\Cookie;

class SeoPagesController extends Controller
{
    public function __construct()
    {
        !Login::permissionAdm() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'seo_pages');
        $this->dynamic('page_name', 'SEO das páginas públicas');
        $this->dynamic('page_action_string', 'SEO das páginas públicas');

        $this->dynamic('list', 'seo-pages/');
        $this->dynamic('insert', 'seo-pages/insert/');
        $this->dynamic('update', 'seo-pages/update/');

    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionPage()
    {

        AdminAssets::init($this);

        $this->setNicknamesToUri(['page']);

        $limit = get('limit') ? get('limit') : 10;

        $page = uri('page') ? uri('page') : 1;

        $model = new SeoPages();

        $success = false;
        if(intval(Session::get('action')) > 0){
            $success = true;
        }

        Session::remove('action');

        $links = 1;

        $model->select()->from(['PAGES' => 'seo_pages']);


        Admin::buildSearchModel($model, [
            'PAGES.page_title',
            'PAGES.slug',
            'PAGES.keywords',
            'PAGES.canonical_url',
            'PAGES.fb_title'
        ]);

        Admin::buildOrderModel($model, [
            'sort_page_title' => 'PAGES.page_title',
            'sort_slug' => 'PAGES.slug',
            'sort_keywords' => 'PAGES.keywords',
            'sort_canonical_url' => 'PAGES.canonical_url',
            'sort_fb_title' => 'PAGES.fb_title',
        ]);

        $paginate = new Paginate($model->count(), $model);
        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links);

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'success' => $success,
            'models' => $data->data,
            'links' => $links,
        ]);
    }

    public function actionInsert()
    {
        $this->dynamic('page_action_string', 'Inserindo');
        AdminAssets::init($this);
        $this->appendJs('news', Winged::$parent . 'assets/js/pages/seo.js');
        $model = new SeoPages();
        Session::always('action', 'insert');
        if (is_get()) {
            $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                'model' => $model
            ]);
        } else if (is_post()) {
            $save = $this->save();
            if (!$save['status']) {
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                ]);
            }
        }
    }

    public function actionDelete()
    {
        $model = new SeoPages();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            if(file_exists($model->folder . $model->fb_image) && is_file($model->folder . $model->fb_image)){
                unlink($model->folder . $model->fb_image);
            }
            $model->remove();
        }
        if (($to = Cookie::get('from_url'))) {
            Cookie::remove('from_url');
            $this->redirectOnly($to);
        } else {
            $this->redirectTo(Winged::$page_surname);
        }
    }

    public function actionUpdate()
    {
        $this->dynamic('page_action_string', 'Alterando');
        AdminAssets::init($this);
        $this->appendJs('news', Winged::$parent . 'assets/js/pages/seo.js');
        $model = new SeoPages();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            if ($model->primaryKey()) {
                Session::always('action', 'update');
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $model
                ]);
            } else {
                if (($to = Cookie::get('from_url'))) {
                    Cookie::remove('from_url');
                    $this->redirectOnly($to);
                } else {
                    $this->redirectTo(Winged::$page_surname);
                }
            }
        } else if (is_post()) {
            $save = $this->save();
            if (!$save['status']) {
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                ]);
            }
        }
    }

    public function save()
    {
        $model = (new SeoPages())->load($_POST);
        if ($model->validate() && ($id = $model->save())) {
            $action = Session::get('action');
            Session::always('action', $id);
            if (($to = Cookie::get('from_url')) && $action == 'update') {
                Cookie::remove('from_url');
                $this->redirectOnly($to);
            } else {
                $this->redirectTo(Winged::$page_surname);
            }
        } else {
            return ['status' => false, 'model' => $model];
        }
    }
}