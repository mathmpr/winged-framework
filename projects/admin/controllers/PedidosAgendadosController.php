<?php

use Winged\Controller\Controller;

use Winged\Winged;
use Winged\Model\Login;
use Winged\Model\Pedidos;
use Winged\Http\Cookie;
use Winged\Http\Session;
use Winged\Database\DbDict;
use Winged\Date\Date;

class PedidosAgendadosController extends Controller
{

    public $message = '';
    public $error_st = '';

    public function __construct()
    {
        !Login::permission(['print']) ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'relatorios');
        $this->dynamic('page_name', 'Próximos pedidos agendados');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'pedidos/');
        $this->dynamic('insert', 'pedidos/insert/');
        $this->dynamic('update', 'pedidos/update/');
    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionDelete()
    {
        $model = new Pedidos();
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

    public function actionPage()
    {
        AdminAssets::init($this);
        $this->appendJs('tokenstags', Winged::$parent . 'assets/js/pages/tokenstags.js');
        $this->appendJs('numeric', Winged::$parent . 'assets/js/pages/numeric.js');
        $this->appendJs('pedidos', Winged::$parent . 'assets/js/pages/pedidos.js');
        $this->setNicknamesToUri(['page']);
        $limit = get('limit') ? get('limit') : 10000;
        $page = uri('page') ? uri('page') : 1;
        $model = new Pedidos();
        $success = null;
        if (!in_array(Session::get('action'), ['insert', 'update']) && Session::get('action') !== false) {
            $success = $model->findOne(Session::get('action'));
        }
        $success = false;
        if (intval(Session::get('action')) > 0) {
            $success = true;
        }

        $warn = intval(Login::current()->warn_days == "" ? 5 : Login::current()->warn_days);

        Session::remove('action');
        $links = 1;
        $model->select(['PEDIDOS.*', 'CLIENTES.nome' => 'cliente', 'USUARIOS.nome' => 'usuario', 'CLIENTES.nome_fantasia', 'CLIENTES.razao_social'])
            ->from(['PEDIDOS' => 'pedidos'])
            ->leftJoin(ELOQUENT_EQUAL, ['CLIENTES' => 'clientes', 'CLIENTES.id_cliente' => 'PEDIDOS.id_cliente'])
            ->leftJoin(ELOQUENT_EQUAL, ['USUARIOS' => 'usuarios', 'USUARIOS.id_usuario' => 'PEDIDOS.id_usuario'])
            ->where(ELOQUENT_BETWEEN, ['PEDIDOS.agendamento' => [(new Date(strtotime(date('Y/m/d 00:00:00'))))->sql(), (new Date(strtotime(date('Y/m/d 00:00:00'))))->add(['d' => $warn])->sql()]])
            ->orderBy(ELOQUENT_DESC, 'PEDIDOS.id_pedido');

        //if (!Login::permissionAdm()) {
        //    $model->andWhere(ELOQUENT_EQUAL, ['PEDIDOS.id_usuario' => Login::current()->primaryKey()]);
        //}

        \Admin::buildSearchModel($model, [
            'CLIENTES.nome',
            'CLIENTES.razao_social',
            'CLIENTES.nome_fantasia',
            'USUARIOS.nome',
            'PEDIDOS.endereco',
            'PEDIDOS.valor_frete',
            'PEDIDOS.status',
            'PEDIDOS.data_cadastro',
            'PEDIDOS.agendamento',
        ], true);


        \Admin::buildOrderModel($model, [
            'sort_nome' => 'CLIENTES.nome',
            'sort_usuario' => 'USUARIOS.nome',
            'sort_endereco' => 'ENDERECO.endereco',
            'sort_valor_frete' => 'PEDIDOS.valor_frete',
            'sort_status' => 'PEDIDOS.status',
            'sort_data_cadastro' => 'PEDIDOS.data_cadastro',
            'sort_agendamento' => 'PEDIDOS.agendamento',
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
}