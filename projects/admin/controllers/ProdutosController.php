<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Http\Cookie;
use Winged\Http\Session;

class ProdutosController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'produtos');
        $this->dynamic('page_name', 'Produtos');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'produtos/');
        $this->dynamic('insert', 'produtos/insert/');
        $this->dynamic('update', 'produtos/update/');
    }
    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }
    public function actionPage()
    {
        AdminAssets::init($this);
        $this->appendJs('carrinho_produtos', Winged::$parent . 'assets/js/pages/carrinho_produtos.js');
        $this->setNicknamesToUri(['page']);
        $limit = get('limit') ? get('limit') : 10;
        $page = uri('page') ? uri('page') : 1;
        $model = new Produtos();
        $success = null;
        if (!in_array(Session::get('action'), ['insert', 'update']) && Session::get('action') !== false) {
            $success = $model->findOne(Session::get('action'));
        }
        $success = false;
        if (intval(Session::get('action')) > 0) {
            $success = true;
        }
        Session::remove('action');
        $links = 1;
        $model->select(['PRODUTOS.*', 'SUBCATEGORIAS.nome' => 'subcategoria', 'CATEGORIAS.nome' => 'categoria'])
            ->from(['PRODUTOS' => 'produtos'])
            ->leftJoin(ELOQUENT_EQUAL, ['CATEGORIAS' => 'produtos_categorias', 'CATEGORIAS.id_categoria' => 'PRODUTOS.id_categoria'])
            ->leftJoin(ELOQUENT_EQUAL, ['SUBCATEGORIAS' => 'produtos_subcategorias', 'SUBCATEGORIAS.id_subcategoria' => 'PRODUTOS.id_subcategoria']);



        \Admin::buildSearchModel($model, [
            'CATEGORIAS.nome',
            'SUBCATEGORIAS.nome',
            'PRODUTOS.nome',
            'PRODUTOS.cod_barras',
            'PRODUTOS.valor_unitario',
            'PRODUTOS.valor_atacado',
            'PRODUTOS.valor_minimo',
            'PRODUTOS.valor_de_custo',
        ]);

        \Admin::buildOrderModel($model, [
            'sort_nome' => 'PRODUTOS.nome',
            'sort_categoria' => 'CATEGORIAS.nome',
            'sort_subcategoria' => 'SUBCATEGORIAS.nome',
            'sort_cod_barras' => 'PRODUTOS.cod_barras',
            'sort_valor_unitario' => 'PRODUTOS.valor_unitario',
            'sort_valor_atacado' => 'PRODUTOS.valor_atacado',
            'sort_valor_minimo' => 'PRODUTOS.valor_minimo',
            'sort_valor_de_custo' => 'PRODUTOS.valor_de_custo',
            'sort_quantidade_estoque' => 'PRODUTOS.quantidade_estoque',
            'sort_status' => 'PRODUTOS.status',
            'sort_data_cadastro' => 'PRODUTOS.data_cadastro',
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
        AdminAssets::init($this);
        $this->appendJs('produtos', Winged::$parent . 'assets/js/pages/produtos.js');
        $this->dynamic('page_action_string', 'Inserindo');
        $model = new Produtos();
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
        $model = new Produtos();
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
        AdminAssets::init($this);
        $this->appendJs('produtos', Winged::$parent . 'assets/js/pages/produtos.js');
        $this->dynamic('page_action_string', 'Alterando');
        $model = new Produtos();
        $this->setNicknamesToUri(['id']);
        Session::always('action', 'update');
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            if ($model->primaryKey()) {
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
        $model = (new Produtos())->load($_POST);
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