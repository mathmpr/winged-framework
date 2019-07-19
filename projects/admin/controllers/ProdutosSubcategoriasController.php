<?php

use Winged\Controller\Controller;
use Winged\Model\Login;
use Winged\Winged;
use Winged\Model\ProdutosSubcategorias;
use Winged\Http\Cookie;
use Winged\Http\Session;

class ProdutosSubcategoriasController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'produtos');
        $this->dynamic('page_name', 'Subcategorias dos produtos');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'produtos_subcategorias/');
        $this->dynamic('insert', 'produtos_subcategorias/insert/');
        $this->dynamic('update', 'produtos_subcategorias/update/');
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
        $model = new ProdutosSubcategorias();
        $success = null;
        if (!in_array(Session::get('action'), ['insert', 'update']) && Session::get('action') !== false) {
            $success = $model->findOne(Session::get('action'));
        }
        $success = false;
        if(intval(Session::get('action')) > 0){
            $success = true;
        }
        Session::remove('action');
        $links = 1;
        $model->select(['SUBCATEGORIAS.nome' => 'subcategoria', 'CATEGORIAS.nome' => 'categoria', 'SUBCATEGORIAS.id_subcategoria'])
            ->from(['SUBCATEGORIAS' => 'produtos_subcategorias'])
            ->leftJoin(ELOQUENT_EQUAL, ['CATEGORIAS' => 'produtos_categorias', 'CATEGORIAS.id_categoria' => 'SUBCATEGORIAS.id_categoria']);
        \Admin::buildSearchModel($model, [
            'SUBCATEGORIAS.nome',
            'CATEGORIAS.nome',
        ]);
        \Admin::buildOrderModel($model, [
            'sort_subcategoria' => 'SUBCATEGORIAS.nome',
            'sort_categoria' => 'CATEGORIAS.nome',
        ]);
        $paginate = new Paginate($model->count(), $model);
        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links, Winged::$page_surname);
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
        $model = new ProdutosSubcategorias();
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
        $model = new ProdutosSubcategorias();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
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
        $model = new ProdutosSubcategorias();
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
        $model = (new ProdutosSubcategorias())->load($_POST);
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