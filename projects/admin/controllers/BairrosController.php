<?php

use Winged\Controller\Controller;
use Winged\Http\Session;
use Winged\Winged;
use Winged\Http\Cookie;

/**
 * Class BairrosController
 */
class BairrosController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'utilidades');
        $this->dynamic('page_name', 'Bairros');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'bairros/');
        $this->dynamic('insert', 'bairros/insert/');
        $this->dynamic('update', 'bairros/update/');
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
        $model = new Bairros();
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
        $model->select(['BAIRROS.nome', 'BAIRROS.id_bairro', 'CIDADES.cidade', 'ESTADOS.estado'])
            ->from(['BAIRROS' => 'bairros'])
            ->leftJoin(ELOQUENT_EQUAL, ['ESTADOS' => 'estados', 'ESTADOS.id_estado' => 'BAIRROS.id_estado'])
            ->leftJoin(ELOQUENT_EQUAL, ['CIDADES' => 'cidades', 'CIDADES.id_cidade' => 'BAIRROS.id_cidade']);
        \Admin::buildSearchModel($model, [
            'CIDADES.cidade',
            'ESTADOS.estado',
            'BAIRROS.nome',
        ]);
        \Admin::buildOrderModel($model, [
            'sort_cidade' => 'CIDADES.cidade',
            'sort_estado' => 'ESTADOS.estado',
            'sort_nome' => 'BAIRROS.nome',
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
        $this->appendJs('bairros', Winged::$parent . 'assets/js/pages/bairros.js');
        $this->dynamic('page_action_string', 'Inserindo');
        $model = new Bairros();
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
        $model = new Bairros();
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
        $this->appendJs('bairros', Winged::$parent . 'assets/js/pages/bairros.js');
        $this->dynamic('page_action_string', 'Alterando');
        $model = new Bairros();
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
        $model = (new Bairros())->load($_POST);
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