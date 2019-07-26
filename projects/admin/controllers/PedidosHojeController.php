<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Database\DbDict;
use Winged\Date\Date;
use Winged\Http\Cookie;
use Winged\Http\Session;

class PedidosHojeController extends Controller
{

    public $message = '';
    public $error_st = '';

    public function __construct()
    {
        !Login::permission(['print']) ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'relatorios');
        $this->dynamic('page_name', 'Pedidos agendados para hoje');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'pedidos/');
        $this->dynamic('insert', 'pedidos/insert/');
        $this->dynamic('update', 'pedidos/update/');
    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionBaixaEstoque()
    {


        if (postset('id_pedido')) {

            $id_pedidos = post('id_pedido');


            $pp = (new PedidosProdutos())
                ->select()
                ->from(['PP' => PedidosProdutos::tableName()])
                ->where(ELOQUENT_IN, ['PP.id_pedido' => $id_pedidos])
                ->orderBy(ELOQUENT_ASC, 'PP.id_produto')
                ->execute();

            $pedidos = (new Pedidos())
                ->select()
                ->from(['PP' => Pedidos::tableName()])
                ->where(ELOQUENT_IN, ['PP.id_pedido' => $id_pedidos])
                ->execute();

            if ($pp) {

                $itens = [];

                $initial_id = $pp[0]->id_produto;

                foreach ($pp as $_pp) {
                    if ($_pp->id_produto != $initial_id) {
                        $initial_id = $_pp->id_produto;
                    }
                    if (!array_key_exists($initial_id, $itens)) {
                        $itens[$initial_id] = [
                            'produto' => $_pp->nome,
                            'quantidade' => 0,
                        ];
                    }
                    $itens[$initial_id]['quantidade'] += $_pp->quantidade;
                }

                $this->partial(Winged::$page_surname . '/' . Winged::$page_surname . '/_pdf', [
                    'pedidos' => $pedidos,
                    'itens' => $itens,
                ]);
            } else {
                if (($to = Cookie::get('from_url'))) {
                    Cookie::remove('from_url');
                    $this->redirectOnly($to);
                } else {
                    $this->redirectTo(Winged::$page_surname);
                }
            }
        } else {
            if (($to = Cookie::get('from_url'))) {
                Cookie::remove('from_url');
                $this->redirectOnly($to);
            } else {
                $this->redirectTo(Winged::$page_surname);
            }
        }
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
        Session::remove('action');
        $links = 1;
        $model->select(['PEDIDOS.*', 'CLIENTES.nome' => 'cliente', 'USUARIOS.nome' => 'usuario', 'CLIENTES.nome_fantasia', 'CLIENTES.razao_social'])
            ->from(['PEDIDOS' => 'pedidos'])
            ->leftJoin(ELOQUENT_EQUAL, ['CLIENTES' => 'clientes', 'CLIENTES.id_cliente' => 'PEDIDOS.id_cliente'])
            ->leftJoin(ELOQUENT_EQUAL, ['USUARIOS' => 'usuarios', 'USUARIOS.id_usuario' => 'PEDIDOS.id_usuario'])
            ->where(ELOQUENT_BETWEEN, ['PEDIDOS.agendamento' => [(new Date(time(), false))->sql() . ' 00:00:00', (new Date(time(), false))->sql() . ' 23:59:59']])
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