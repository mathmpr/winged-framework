<?php

use Winged\Winged;
use Winged\Http\Cookie;
use Winged\Http\Session;
use Winged\Controller\Controller;
use Winged\Database\DbDict;

class ObrasController extends Controller
{
    public function __construct()
    {
        !Login::permissionAdm() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'obras');
        $this->dynamic('page_name', 'Obras');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'obras/');
        $this->dynamic('insert', 'obras/insert/');
        $this->dynamic('update', 'obras/update/');
    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionClientes()
    {
        if (is_post()) {
            $get = (new Clientes())
                ->select()
                ->from(['USUARIOS' => 'usuarios'])
                ->where(ELOQUENT_LIKE, ['LCASE(USUARIOS.nome)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(USUARIOS.nome_fantasia)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(USUARIOS.razao_social)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(USUARIOS.email)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(USUARIOS.cpf)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(USUARIOS.cnpj)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->andWhere(ELOQUENT_EQUAL, ['USUARIOS.session_namespace' => 'FUN'])
                ->execute();

            $data = [];

            if ($get) {
                $x = 0;
                foreach ($get as $g) {
                    $data[$x] = [
                        'id_usuario' => $g->primaryKey(),
                        'nome' => $g->nome == '' ? ucwords(mb_strtolower($g->nome_fantasia, 'UTF-8')) : ucwords(mb_strtolower($g->nome, 'UTF-8')),
                    ];

                    $data[$x]['nome'] .= ' : ' . ucwords(mb_strtolower($g->nome_fantasia, 'UTF-8'));

                    $data[$x]['nome'] .= $g->cnpj == '' ? ' : ' . ucwords(mb_strtolower($g->cpf, 'UTF-8')) : ' : ' . ucwords(mb_strtolower($g->cnpj, 'UTF-8'));

                    $x++;

                }
                return ['data' => $data];
            }
            return ['data' => []];
        }
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
        $model->select()->from(['OBRAS' => 'obras']);

        Admin::buildSearchModel($model, [
            'OBRAS.nome',
            'OBRAS.rua'
        ]);
        Admin::buildOrderModel($model, [
            'sort_nome' => 'OBRAS.nome',
            'sort_rua' => 'OBRAS.rua'
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
        $this->appendJs('tokenstags', Winged::$parent . 'assets/js/pages/tokenstags.js');
        $this->appendJs('numeric', Winged::$parent . 'assets/js/pages/numeric.js');
        $this->appendJs("cep", "./projects/admin/assets/js/pages/cep.js");
        $this->appendJs("obras", "./projects/admin/assets/js/pages/obras.js");
        $this->appendJs('_mask', '<script> 
    $(function(){
        $("#Obras_cep").mask("99.999-999");
    });
</script>');
        $model = new Obras();

        $clientes = ['data' => []];

        Session::always('action', 'insert');
        if (is_get()) {
            $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                'model' => $model,
                'clientes' => $clientes,
            ]);
        } else if (is_post()) {
            $save = $this->save();
            if (!$save['status']) {

                $cliente = (new Usuarios())->select()
                    ->from(['USUARIOS' => 'usuarios'])
                    ->where(ELOQUENT_EQUAL, ['USUARIOS.id_usuario' => $save['model']->id_usuario])
                    ->execute(true);

                if ($cliente) {
                    $cliente = (object)$cliente[0];
                    $cliente->nome = ($cliente->nome == '' ? ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8')) : ucwords(mb_strtolower($cliente->nome, 'UTF-8'))) . (' : ' . ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8'))) . ($cliente->cnpj == '' ? ' : ' . ucwords(mb_strtolower($cliente->cpf, 'UTF-8')) : ' : ' . ucwords(mb_strtolower($cliente->cnpj, 'UTF-8')));
                }

                $clientes = ['data' => [(array)$cliente]];

                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                    'clientes' => $clientes,
                ]);
            }
        }
    }

    public function actionDelete()
    {
        $model = new Obras();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            if ($model->autoLoadDb(uri('id'))) {
                $model->remove();
            }
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
        $this->appendJs('tokenstags', Winged::$parent . 'assets/js/pages/tokenstags.js');
        $this->appendJs('numeric', Winged::$parent . 'assets/js/pages/numeric.js');
        $this->appendJs("cep", "./projects/admin/assets/js/pages/cep.js");
        $this->appendJs("obras", "./projects/admin/assets/js/pages/obras.js");
        $this->appendJs('_mask', '<script> 
    $(function(){        
        $("#Obras_cep").mask("99.999-999");
    });
</script>');
        $model = new Obras();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));

            $cliente = (new Usuarios())->select()
                ->from(['USUARIOS' => 'usuarios'])
                ->where(ELOQUENT_EQUAL, ['USUARIOS.id_usuario' => $model->id_usuario])
                ->execute(true);

            if ($cliente) {
                $cliente = (object)$cliente[0];
                $cliente->nome = ($cliente->nome == '' ? ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8')) : ucwords(mb_strtolower($cliente->nome, 'UTF-8'))) . (' : ' . ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8'))) . ($cliente->cnpj == '' ? ' : ' . ucwords(mb_strtolower($cliente->cpf, 'UTF-8')) : ' : ' . ucwords(mb_strtolower($cliente->cnpj, 'UTF-8')));
            }

            $clientes = ['data' => [(array)$cliente]];

            if ($model->primaryKey()) {
                Session::always('action', 'update');
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $model,
                    'clientes' => $clientes
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

                $cliente = (new Usuarios())->select()
                    ->from(['USUARIOS' => 'usuarios'])
                    ->where(ELOQUENT_EQUAL, ['USUARIOS.id_usuario' => $save['model']->id_usuario])
                    ->execute(true);

                if ($cliente) {
                    $cliente = (object)$cliente[0];
                    $cliente->nome = ($cliente->nome == '' ? ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8')) : ucwords(mb_strtolower($cliente->nome, 'UTF-8'))) . (' : ' . ucwords(mb_strtolower($cliente->nome_fantasia, 'UTF-8'))) . ($cliente->cnpj == '' ? ' : ' . ucwords(mb_strtolower($cliente->cpf, 'UTF-8')) : ' : ' . ucwords(mb_strtolower($cliente->cnpj, 'UTF-8')));
                }

                $clientes = ['data' => [(array)$cliente]];

                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                    'clientes' => $clientes
                ]);
            }
        }
    }

    public function save()
    {
        $to_load = [];
        $to_load['Obras'] = $_POST['Obras'];
        $to_load['Obras']['id_usuario'] = $_POST['id_usuario']['id_usuario'][0];
        $model = (new Obras())->load($to_load);

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