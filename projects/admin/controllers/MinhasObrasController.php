<?php

use Winged\Winged;
use Winged\Http\Cookie;
use Winged\Http\Session;
use Winged\Controller\Controller;
use Winged\Database\DbDict;

class MinhasObrasController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'obras');
        $this->dynamic('page_name', 'Minhas obras');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'minhas-obras/');
        $this->dynamic('insert', 'minhas-obras/insert/');
        $this->dynamic('update', 'minhas-obras/update/');
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
        $model = new Obras();
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
        $model->select()->from(['OBRAS' => 'obras'])->where(ELOQUENT_EQUAL, ['OBRAS.id_usuario' => Login::current()->primaryKey()])->andWhere(ELOQUENT_EQUAL, ['OBRAS.status' => 1]);

        Admin::buildSearchModel($model, [
            'OBRAS.nome',
            'OBRAS.rua'
        ], true);
        Admin::buildOrderModel($model, [
            'sort_nome' => 'OBRAS.nome',
            'sort_rua' => 'OBRAS.rua'
        ], true);

        $paginate = new Paginate($model->count(), $model);
        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links, Winged::$page_surname);
        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'success' => $success,
            'models' => $data->data,
            'links' => $links,
        ]);
    }


    public function actionVisualizar(){
        AdminAssets::init($this);

        $this->dynamic('page_action_string', 'Visualizando');

        $this->setNicknamesToUri(['id']);

        $model = (new Obras)->select()
            ->from(['OBRAS' => 'obras'])
            ->where(ELOQUENT_EQUAL, ['OBRAS.id_usuario' => Login::current()->primaryKey()])
            ->andWhere(ELOQUENT_EQUAL, ['OBRAS.status' => 1])
            ->andWhere(ELOQUENT_EQUAL, ['OBRAS.id_obra' => uri('id')])->one();

        if(!$model){
            $this->redirectTo('minhas-obras');
        }

        $model->estadoNome = (new Estados)->select()
            ->from(['E' => 'estados'])
            ->where(ELOQUENT_EQUAL, ['E.id_estado' => $model->estado])->one()->estado;

        $model->cidadeNome = (new Cidades)->select()
            ->from(['C' => 'cidades'])
            ->where(ELOQUENT_EQUAL, ['C.id_cidade' => $model->cidade])->one()->cidade;

        $model->bairroNome = (new Bairros)->select()
            ->from(['B' => 'bairros'])
            ->where(ELOQUENT_EQUAL, ['B.id_bairro' => $model->bairro])->one()->nome;

        $usuario = Login::current();

        $usuario->estadoNome = (new Estados)->select()
            ->from(['E' => 'estados'])
            ->where(ELOQUENT_EQUAL, ['E.id_estado' => $usuario->estado])->one()->estado;

        $usuario->cidadeNome = (new Cidades)->select()
            ->from(['C' => 'cidades'])
            ->where(ELOQUENT_EQUAL, ['C.id_cidade' => $usuario->cidade])->one()->cidade;

        $usuario->bairroNome = (new Bairros)->select()
            ->from(['B' => 'bairros'])
            ->where(ELOQUENT_EQUAL, ['B.id_bairro' => $usuario->bairro])->one()->nome;

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
            'model' => $model,
            'usuario' => $usuario,
        ]);
    }

}