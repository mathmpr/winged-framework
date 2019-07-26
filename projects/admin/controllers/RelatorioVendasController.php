<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Database\DbDict;
use Winged\Http\Session;
use Winged\Date\Date;

class RelatorioVendasController extends Controller
{

    public $message = '';
    public $error_st = '';

    public function __construct()
    {
        !Login::permission(['print']) ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'relatorios');
        $this->dynamic('page_name', 'Relatório de vendas');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'relatorio-vendas/');
        $this->dynamic('insert', 'relatorio-vendas/insert/');
        $this->dynamic('generate', 'relatorio-vendas/generate/');
        $this->dynamic('update', 'relatorio-vendas/result/');
    }

    public function actionIndex()
    {
        AdminAssets::init($this);
        $this->appendJs('ralatorio_vendas', Winged::$parent . 'assets/js/pages/ralatorio_vendas.js');

        $model = $this->completeModel((new RelatorioVendas()));

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'model' => $model,
        ]);
    }

    public function actionStatus()
    {
        return [
            'data' => [
                [
                    'id_status' => 1,
                    'nome' => 'Aguardando separação',
                ],
                [
                    'id_status' => 2,
                    'nome' => 'Encaminhado',
                ],
                [
                    'id_status' => 3,
                    'nome' => 'Entregue',
                ],
                [
                    'id_status' => 4,
                    'nome' => 'Cancelado',
                ]
            ]
        ];
    }

    public function actionGenerate()
    {

    }

    public function completeModel($model)
    {
        if (get_class($model) === 'Winged\Model\RelatorioVendas') {

            if ($clientes = array_key_exists_check('id_cliente', $model->clientes)) {

                $get = (new Clientes())
                    ->select()
                    ->from(['CLIENTES' => 'clientes'])
                    ->where(ELOQUENT_IN, ['CLIENTES.id_cliente' => $clientes])
                    ->execute();

                $data = [];

                if ($get) {
                    $nome = '';
                    $x = 0;
                    foreach ($get as $g) {
                        $data[$x] = [
                            'id_cliente' => $g->primaryKey(),
                            'nome' => $g->nome == '' ? ucwords(mb_strtolower($g->nome_fantasia, 'UTF-8')) : ucwords(mb_strtolower($g->nome, 'UTF-8')),
                        ];

                        $data[$x]['nome'] .= ' : ' . ucwords(mb_strtolower($g->nome_fantasia, 'UTF-8'));

                        $data[$x]['nome'] .= $g->cnpj == '' ? ' : ' . ucwords(mb_strtolower($g->cpf, 'UTF-8')) : ' : ' . ucwords(mb_strtolower($g->cnpj, 'UTF-8'));

                        $x++;

                    }
                    $model->clientes = [
                        'data' => $data
                    ];
                } else {
                    $model->clientes = [
                        'data' => [

                        ]
                    ];
                }
            } else {
                $model->clientes = [
                    'data' => [

                    ]
                ];
            }

            if ($bairros = array_key_exists_check('id_bairro', $model->bairros)) {
                $get = (new Bairros())
                    ->select()
                    ->from(['BAIRROS' => Bairros::tableName()])
                    ->where(ELOQUENT_IN, ['BAIRROS.id_bairro' => $bairros]);

                $get = $get->execute();

                $data = [];

                if ($get) {
                    $x = 0;
                    foreach ($get as $g) {
                        $data[$x] = [
                            'id_bairro' => $g->primaryKey(),
                            'nome' => $g->nome,
                        ];
                        $x++;
                    }
                    $model->bairros = [
                        'data' => $data
                    ];
                } else {
                    $model->bairros = [
                        'data' => [

                        ]
                    ];
                }
            } else {
                $model->bairros = [
                    'data' => [

                    ]
                ];
            }

            if ($status = array_key_exists_check('id_status', $model->status)) {
                $base = [
                    1 => 'Aguardando separação',
                    2 => 'Encaminhado',
                    3 => 'Entregue',
                    4 => 'Cancelado',
                ];

                $ny = [
                    'data' => [

                    ]
                ];

                foreach ($base as $key => $value) {
                    if (in_array($key, $status)) {
                        $ny['data'][] = [
                            'id_status' => $key,
                            'nome' => $value,
                        ];
                    }
                }

                $model->status = $ny;

            } else {
                $model->status = [
                    'data' => [

                    ]
                ];
            }

            if ($produtos = array_key_exists_check('id_produto', $model->produtos)) {

                $get = (new Produtos())
                    ->select(['PRODUTOS.nome', 'PRODUTOS.id_produto', 'PRODUTOS.cod_barras'])
                    ->from(['PRODUTOS' => 'produtos'])
                    ->leftJoin(ELOQUENT_EQUAL, ['CATEGORIAS' => 'produtos_categorias', 'CATEGORIAS.id_categoria' => 'PRODUTOS.id_categoria'])
                    ->leftJoin(ELOQUENT_EQUAL, ['SUBCATEGORIAS' => 'produtos_subcategorias', 'SUBCATEGORIAS.id_subcategoria' => 'PRODUTOS.id_subcategoria'])
                    ->where(ELOQUENT_IN, ['PRODUTOS.id_produto' => $produtos])
                    ->execute();

                $data = [];

                if ($get) {
                    $x = 0;
                    foreach ($get as $g) {
                        $data[$x] = [
                            'id_produto' => $g->id_produto,
                            'nome' => $g->nome . ' : ' . $g->cod_barras,
                        ];
                        $x++;
                    }
                    $model->produtos = [
                        'data' => $data
                    ];
                } else {
                    $model->produtos = [
                        'data' => [

                        ]
                    ];
                }
            } else {
                $model->produtos = [
                    'data' => [

                    ]
                ];
            }

        }

        return $model;

    }

    public function actionResult()
    {
        AdminAssets::init($this);
        $this->appendJs('pedidos', Winged::$parent . 'assets/js/pages/pedidos.js');
        $this->appendJs('relatorio_vendas', Winged::$parent . 'assets/js/pages/ralatorio_vendas.js');

        $model = new RelatorioVendas();

        if (is_get()) {
            if (!empty(Winged::get())) {
                Session::always('relatorio_vendas_get', $_GET);
            }
            if ($post = Session::get('relatorio_vendas_post')) {
                $model->load(Session::get('relatorio_vendas_post'));
            }
        } else {
            $model->load($_POST);
        }

        if ($model->validate()) {

            $pedidos = (new Pedidos())
                ->select(['P.*', 'C.nome' => 'cliente', 'U.nome' => 'usuario', 'C.nome_fantasia', 'C.razao_social'])
                ->from(['P' => 'pedidos'])
                ->leftJoin(ELOQUENT_EQUAL, ['C' => 'clientes', 'C.id_cliente' => 'P.id_cliente'])
                ->leftJoin(ELOQUENT_EQUAL, ['U' => 'usuarios', 'U.id_usuario' => 'P.id_usuario'])
                ->where(ELOQUENT_LARGER, ['P.data_cadastro' => (new Date($model->data_inicial))->sql()])
                ->andWhere(ELOQUENT_SMALLER, ['P.data_cadastro' => (new Date($model->data_final))->sql()]);

            if ($clientes = array_key_exists_check('id_cliente', $model->clientes)) {
                $pedidos->andWhere(ELOQUENT_IN, ['C.id_cliente' => $clientes]);
            }

            if ($bairros = array_key_exists_check('id_bairro', $model->bairros)) {
                $pedidos->andWhere(ELOQUENT_IN, ['P.id_bairro' => $bairros]);
            }

            if ($status = array_key_exists_check('id_status', $model->status)) {
                $pedidos->andWhere(ELOQUENT_IN, ['P.status' => $status]);
            }

            \Admin::buildSearchModel($pedidos, [
                'C.nome',
                'U.nome',
                'P.endereco',
                'P.valor_frete',
                'P.status',
                'P.data_cadastro',
                'P.agendamento',
            ], true);

            \Admin::buildOrderModel($pedidos, [
                'sort_nome' => 'C.nome',
                'sort_usuario' => 'U.nome',
                'sort_endereco' => 'P.endereco',
                'sort_valor_frete' => 'P.valor_frete',
                'sort_status' => 'P.status',
                'sort_data_cadastro' => 'P.data_cadastro',
                'sort_agendamento' => 'P.agendamento',
            ]);

            $pedidos = $pedidos->execute();

            if ($pedidos) {

                if ($produtos = array_key_exists_check('id_produto', $model->produtos)) {
                    foreach ($pedidos as $key => $pedido) {
                        $pps = (new PedidosProdutos())
                            ->select()
                            ->from(['PP' => PedidosProdutos::tableName()])
                            ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $pedido->primaryKey()])
                            ->andWhere(ELOQUENT_IN, ['PP.id_produto' => $produtos])
                            ->execute(true);

                        if (!$pps) {
                            unset($pedidos[$key]);
                        }
                    }
                }
            } else {
                $pedidos = [];
            }

            if (is_post()) {
                Session::always('relatorio_vendas_post', $_POST);
            }

            $nmodel = $this->completeModel($model);

            $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_result', [
                'models' => $pedidos,
                'nmodel' => $nmodel,
            ]);

        } else {

            $model = $this->completeModel($model);

            $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
                'model' => $model,
            ]);
        }
    }

    public function actionBairros()
    {
        if (is_post()) {

            $query = explode(',', post('query'));

            $estado = false;
            $cidade = false;
            $search = false;

            if (count($query) > 1) {
                if (count($query) == 2) {
                    $cidade = trim($query[0]);
                    $search = trim($query[1]);
                }
                if (count($query) == 3) {
                    $cidade = trim($query[0]);
                    $estado = trim($query[1]);
                    $search = trim($query[2]);
                }
            } else {
                $search = trim($query[0]);
            }

            $get = (new Bairros())
                ->select()
                ->from(['BAIRROS' => Bairros::tableName()])
                ->leftJoin(ELOQUENT_EQUAL, ['CIDADES' => Cidades::tableName(), 'CIDADES.id_cidade' => 'BAIRROS.id_cidade'])
                ->leftJoin(ELOQUENT_EQUAL, ['ESTADOS' => Estados::tableName(), 'ESTADOS.id_estado' => 'BAIRROS.id_estado'])
                ->where(ELOQUENT_LIKE, ['LCASE(BAIRROS.nome)' => '%' . mb_strtolower($search, 'UTF-8') . '%']);

            if ($estado) {
                $get->andWhere(ELOQUENT_LIKE, ['LCASE(ESTADOS.estado)' => '%' . mb_strtolower($estado, 'UTF-8') . '%']);
            }

            if ($cidade) {
                $get->andWhere(ELOQUENT_LIKE, ['LCASE(CIDADES.cidade)' => '%' . mb_strtolower($cidade, 'UTF-8') . '%']);
            }

            $get = $get->execute();

            $data = [];

            if ($get) {
                $x = 0;
                foreach ($get as $g) {
                    $data[$x] = [
                        'id_bairro' => $g->primaryKey(),
                        'nome' => $g->nome,
                    ];
                    $x++;
                }
                return ['data' => $data];
            }
            return ['data' => []];
        }
    }
}