<?php

use Winged\Controller\Controller;
use Winged\Model\Login;
use Winged\Winged;
use Winged\Model\Pedidos;
use Winged\Database\DbDict;
use Winged\Model\Usuarios;
use Winged\Model\Clientes;
use Winged\Model\Produtos;
use Winged\Date\Date;
use Winged\Http\Cookie;
use Winged\Formater\Formater;
use Winged\Http\Session;
use Winged\Utils\Caster;

class PedidosController extends Controller
{

    public $message = '';
    public $error_st = '';

    public function __construct()
    {
        !Login::permission(['print']) ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'utilidades');
        $this->dynamic('page_name', 'Pedidos');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'pedidos/');
        $this->dynamic('insert', 'pedidos/insert/');
        $this->dynamic('update', 'pedidos/update/');
    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    private function checkPermission()
    {
        $pedido = (new Pedidos())->select()
            ->from(['P' => Pedidos::tableName()])
            ->where(ELOQUENT_EQUAL, ['P.id_pedido' => post('id_pedido')])
            ->andWhere(ELOQUENT_EQUAL, ['P.id_usuario' => Login::current()->primaryKey()])
            ->one();

        if ($pedido) {
            if ($pedido->status == 3 || $pedido->status == 4) {
                $this->error_st = 'status block';
                $this->message = 'Você não pode alterar um pedido entregue ou cancelado.';
                return false;
            }
            return true;
        }

        if (Login::currentIsAdm()) {

            $pedido = (new Pedidos())->select()
                ->from(['P' => Pedidos::tableName()])
                ->where(ELOQUENT_EQUAL, ['P.id_pedido' => post('id_pedido')])
                ->one();

            /**
             * @var $usuario Usuarios
             */

            if ($pedido) {
                return true;
            } else {
                $this->error_st = 'no exists';
                $this->message = 'O pedido não existe mais.';
                return false;
            }
        }

        $this->error_st = 'permission denied';
        $this->message = 'Você não é dono do pedido, então não pode alterar o mesmo.';

        return false;

    }

    private function recalc()
    {

        $pedidos = (new Pedidos())->select()->from(['P' => Pedidos::tableName()])->where(ELOQUENT_EQUAL, ['P.id_pedido' => post('id_pedido')])->one();

        $pedidos_produtos = (new PedidosProdutos())->select()
            ->from(['PP' => PedidosProdutos::tableName()])
            ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => post('id_pedido')])
            ->execute();

        $quantidade_total = 0;
        $valor_total = 0;
        $media_desconto = 0;

        /**
         * @var $pp      PedidosProdutos
         * @var $pedidos Pedidos
         */
        if ($pedidos && $pedidos_produtos) {
            foreach ($pedidos_produtos as $pp) {
                $quantidade_total += $pp->quantidade;
                $valor_total += ($pp->valor_unico - (($pp->valor_unico * $pp->porcentagem_desconto / 100))) * $pp->quantidade;
                $media_desconto += $pp->porcentagem_desconto;
            }
            $valor_total += $pedidos->valor_frete;
        } else {
            $pedidos_produtos = [null];
        }

        return [
            'quantidade_total' => $quantidade_total,
            'valor_total' => $valor_total,
            'media_desconto' => number_format($media_desconto / count($pedidos_produtos), 2),
        ];

    }

    public function actionPrint()
    {

        $this->setNicknamesToUri(['id']);
        if (uri('id')) {

            $id = uri('id');

            $id = explode('_', $id);

            $model = (new Pedidos())
                ->select(['P.*', 'C.nome', 'C.razao_social', 'C.nome_fantasia', 'C.inscricao_estadual', 'C.cnpj', 'U.telefone', 'U.nome' => 'usuario'])
                ->from(['P' => Pedidos::tableName()])
                ->leftJoin(ELOQUENT_EQUAL, ['C' => Clientes::tableName(), 'C.id_cliente' => 'P.id_cliente'])
                ->leftJoin(ELOQUENT_EQUAL, ['U' => Usuarios::tableName(), 'U.id_usuario' => 'P.id_usuario'])
                ->where(ELOQUENT_EQUAL, ['P.data_cadastro' => (new Date((int)$id[0]))->sql()])
                ->andWhere(ELOQUENT_EQUAL, ['P.id_pedido' => $id[1]])
                ->one();


            if ($model) {

                $model->extras->telefone = $model->extras->telefone == '' ? 'N/A' : $model->extras->telefone;

                $pp = (new PedidosProdutos())
                    ->select()
                    ->from(['PP' => PedidosProdutos::tableName()])
                    ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $model->primaryKey()])
                    ->execute();

                $this->partial(Winged::$page_surname . '/' . Winged::$page_surname . '/_pdf', [
                    'pedido' => $model,
                    'pedido_produto' => $pp,
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

    public function actionUpdate()
    {
        if (is_post()) {

            $_POST['valor_frete'] = preg_replace("/[^0-9]/", "", $_POST['valor_frete']);

            if ($this->checkPermission()) {

                if ($_POST['agendamento'] == '') {
                    $_POST['agendamento'] = (new Date())->add(['s' => 1000])->dmy();
                } else {
                    $_POST['agendamento'] .= ':00';
                }

                /**
                 * @var $pedido Pedidos
                 */

                $pedido = (new Pedidos())->findOne(post('id_pedido'));

                $pedido->load([
                    'Pedidos' => [
                        'agendamento' => (new Date(post('agendamento')))->sql(),
                        'status' => post('status'),
                        'observacoes' => nltobr(post('observacoes')),
                        'metodo_pagamento' => post('metodo_pagamento'),
                        'valor_frete' => post('valor_frete'),
                        'innf' => post('innf'),
                    ],
                ]);

                if ($pedido->save()) {

                    $recalc = $this->recalc();

                    if ($pedido->status == 4 && $pedido->innf != 1) {

                        $pedidos_produtos = (new PedidosProdutos())->select()
                            ->from(['PP' => PedidosProdutos::tableName()])
                            ->leftJoin(ELOQUENT_EQUAL, ['PR' => Produtos::tableName(), 'PR.id_produto' => 'PP.id_produto'])
                            ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $pedido->primaryKey()])
                            ->execute();

                        if ($pedidos_produtos) {
                            foreach ($pedidos_produtos as $pp) {
                                (new Produtos())->update(['PR' => Produtos::tableName()])
                                    ->set([
                                        'quantidade_estoque' => $pp->extra()->quantidade_estoque + $pp->quantidade,
                                    ])
                                    ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $pp->id_produto])
                                    ->execute();
                            }
                        }

                    } else if ($pedido->status != 4 && $pedido->innf == 1) {
                        $pedidos_produtos = (new PedidosProdutos())->select()
                            ->from(['PP' => PedidosProdutos::tableName()])
                            ->leftJoin(ELOQUENT_EQUAL, ['PR' => Produtos::tableName(), 'PR.id_produto' => 'PP.id_produto'])
                            ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $pedido->primaryKey()])
                            ->execute();

                        if ($pedidos_produtos) {
                            foreach ($pedidos_produtos as $pp) {
                                (new Produtos())->update(['PR' => Produtos::tableName()])
                                    ->set([
                                        'quantidade_estoque' => $pp->extra()->quantidade_estoque + $pp->quantidade,
                                    ])
                                    ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $pp->id_produto])
                                    ->execute();
                            }
                        }
                    } else if ($pedido->status != 4 && $pedido->innf != 1) {
                        $pedidos_produtos = (new PedidosProdutos())->select()
                            ->from(['PP' => PedidosProdutos::tableName()])
                            ->leftJoin(ELOQUENT_EQUAL, ['PR' => Produtos::tableName(), 'PR.id_produto' => 'PP.id_produto'])
                            ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $pedido->primaryKey()])
                            ->execute();

                        if ($pedidos_produtos) {
                            foreach ($pedidos_produtos as $pp) {
                                (new Produtos())->update(['PR' => Produtos::tableName()])
                                    ->set([
                                        'quantidade_estoque' => $pp->extra()->quantidade_estoque - $pp->quantidade,
                                    ])
                                    ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $pp->id_produto])
                                    ->execute();
                            }
                        }
                    }

                    return [
                        'status' => true,
                        'st_text' => $pedido->getStatus(),
                        'st_color' => $pedido->getStatusColor(),
                        'current' => $pedido->status,
                        'innf' => post('innf'),
                        'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                    ];

                } else {
                    return [
                        'status' => false,
                        'error' => 'db error',
                        'message' => 'Ocorreu um erro ao alterar o pedido, tente novamente em alguns instantes.'
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'error' => $this->error_st,
                    'message' => $this->message
                ];
            }
        }
    }

    public function actionRemove()
    {
        if (is_post()) {

            if ($this->checkPermission()) {

                $remove = false;

                $pp = (new PedidosProdutos())->select()
                    ->from(['PP' => PedidosProdutos::tableName()])
                    ->leftJoin(ELOQUENT_EQUAL, ['PR' => Produtos::tableName(), 'PR.id_produto' => 'PP.id_produto'])
                    ->where(ELOQUENT_EQUAL, ['PP.id_pedido_produto' => post('id_pedido_produto')])
                    ->one();

                if ($pp) {
                    $remove = (new PedidosProdutos())->delete(['PP' => PedidosProdutos::tableName()])
                        ->where(ELOQUENT_EQUAL, ['PP.id_pedido_produto' => post('id_pedido_produto')])
                        ->execute();

                    if ($remove) {
                        (new Produtos())->update(['PR' => Produtos::tableName()])
                            ->set([
                                'quantidade_estoque' => $pp->extra()->quantidade_estoque + $pp->quantidade,
                            ])
                            ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $pp->id_produto])
                            ->execute();
                    }
                }

                $recalc = $this->recalc();

                if ($recalc['quantidade_total'] == 0) {
                    (new Pedidos())
                        ->delete(['P' => Pedidos::tableName()])
                        ->where(ELOQUENT_EQUAL, ['P.id_pedido' => post('id_pedido')])
                        ->execute();
                }

                return [
                    'status' => $remove,
                    'quantidade_total' => $recalc['quantidade_total'],
                    'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                    'media_desconto' => $recalc['media_desconto'],
                ];

            } else {
                return [
                    'status' => false,
                    'error' => $this->error_st,
                    'message' => $this->message
                ];
            }
        }
    }

    public function actionAdd()
    {
        if (is_post()) {

            if (postset('produto')) {

                if ($this->checkPermission()) {
                    $id_produto = post('produto')['id_produto'];

                    if (!is_array($id_produto)) {
                        $id_produto = [$id_produto];
                    }

                    $no_exists = [];

                    $pedido = (new Pedidos())->select()
                        ->from(['P' => Pedidos::tableName()])
                        ->where(ELOQUENT_EQUAL, ['P.id_pedido' => post('id_pedido')])
                        ->one();

                    $get = (new PedidosProdutos())->select()
                        ->from(['PP' => PedidosProdutos::tableName()])
                        ->leftJoin(ELOQUENT_EQUAL, ['P' => Pedidos::tableName(), 'PP.id_pedido' => 'P.id_pedido'])
                        ->leftJoin(ELOQUENT_EQUAL, ['PR' => Produtos::tableName(), 'PR.id_produto' => 'PP.id_produto'])
                        ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => post('id_pedido')])
                        ->andWhere(ELOQUENT_EQUAL, ['P.id_usuario' => Login::current()->primaryKey()])
                        ->execute(true);

                    $ids_ = [];

                    if ($get) {
                        $ids_ = array_column($get, 'id_produto');
                    }

                    $exists = array_intersect($id_produto, $ids_);

                    foreach ($id_produto as $id) {
                        if (!in_array($id, $exists)) {
                            $no_exists[] = $id;
                        }
                    }

                    $updates = ['updates' => [], 'inserts' => []];

                    foreach ($exists as $ids) {
                        $found = false;
                        foreach ($get as $value) {
                            if ($value['id_produto'] == $ids) {
                                $found = $value;
                                break;
                            }
                        }
                        if ($found) {

                            if (($found['quantidade'] + 1) < $found['quantidade_estoque']) {
                                (new PedidosProdutos())->update(['PP' => PedidosProdutos::tableName()])
                                    ->set([
                                        'quantidade' => $found['quantidade'] + 1,
                                    ])
                                    ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => post('id_pedido')])
                                    ->andWhere(ELOQUENT_EQUAL, ['PP.id_pedido_produto' => $found['id_pedido_produto']])
                                    ->execute();
                                if (($pedido->innf == 0 || $pedido->innf == '')) {
                                    (new Produtos())->update(['PR' => Produtos::tableName()])
                                        ->set([
                                            'quantidade_estoque' => $found['quantidade_estoque'] - 1,
                                        ])
                                        ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $ids])
                                        ->execute();
                                }
                            }

                            $updates['updates'][] = [
                                'id_pedido_produto' => $found['id_pedido_produto'],
                                'quantidade' => (($found['quantidade'] + 1) > $found['quantidade_estoque']) ? $found['quantidade'] : $found['quantidade'] + 1,
                                'valor_final' => Formater::intToCurrency(($found['valor_unico'] - ($found['valor_unico'] * $found['porcentagem_desconto'] / 100)) * ($found['quantidade'] + 1)),
                                'valor_unico' => Formater::intToCurrency($found['valor_unico']),
                                'porcentagem_desconto' => $found['porcentagem_desconto'],
                                'nome' => $found['nome'],
                                'error' => (($found['quantidade'] + 1) > $found['quantidade_estoque']) ? true : false,
                            ];
                        }
                    }

                    if (count($no_exists) > 0) {
                        $get = (new Produtos())->select()
                            ->from(['PR' => Produtos::tableName()])
                            ->where(ELOQUENT_IN, ['PR.id_produto' => $no_exists])
                            ->execute();

                        if ($get) {
                            /**
                             * @var $get Produtos[]
                             */
                            foreach ($get as $produto) {

                                $loaded = (new PedidosProdutos())->load([
                                    'PedidosProdutos' => [
                                        'id_pedido' => post('id_pedido'),
                                        'id_produto' => $produto->id_produto,
                                        'nome' => $produto->nome,
                                        'valor_unico' => $produto->valor_atacado * 100,
                                        'quantidade' => 1,
                                        'porcentagem_desconto' => 0,
                                    ]
                                ]);

                                if ($produto->quantidade_estoque > 1) {
                                    $save = $loaded->save();
                                    if (($pedido->innf == 0 || $pedido->innf == '') && $save) {
                                        (new Produtos())->update(['PR' => Produtos::tableName()])
                                            ->set([
                                                'quantidade_estoque' => $produto->quantidade_estoque - 1,
                                            ])
                                            ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $produto->primaryKey()])
                                            ->execute();
                                    }
                                }

                                $loaded->valor_final = Formater::intToCurrency(($loaded->valor_unico - ($loaded->valor_unico * $loaded->porcentagem_desconto / 100)) * $loaded->quantidade);

                                $loaded->valor_unico = Formater::intToCurrency($loaded->valor_unico);

                                $updates['inserts'][] = $loaded;
                            }
                        }
                    }

                    $recalc = $this->recalc();

                    return ['status' => true, 'data' => $updates, 'quantidade_total' => $recalc['quantidade_total'], 'valor_total' => Formater::intToCurrency($recalc['valor_total']), 'media_desconto' => $recalc['media_desconto']];
                }
                return [
                    'status' => false,
                    'error' => $this->error_st,
                    'message' => $this->message
                ];
            }
            return ['status' => false, 'error' => 'no product', 'message' => 'Você não selecionou nenhum produto.'];
        }
    }

    public function actionRefresh()
    {
        if (is_post()) {

            if ($this->checkPermission()) {
                $_POST['valor_unico'] = preg_replace("/[^0-9]/", "", $_POST['valor_unico']);
                $_POST['quantidade'] = $_POST['quantidade'] == '' ? 0 : preg_replace("/[^0-9]/", "", $_POST['quantidade']);

                $model = new PedidosProdutos();
                $model = $model->select(['PP.*', 'PRODUTOS.*', 'PEDIDOS.innf'])
                    ->from(['PP' => PedidosProdutos::tableName()])
                    ->leftJoin(ELOQUENT_EQUAL, ['PRODUTOS' => 'produtos', 'PRODUTOS.id_produto' => 'PP.id_produto'])
                    ->leftJoin(ELOQUENT_EQUAL, ['PEDIDOS' => 'pedidos', 'PEDIDOS.id_pedido' => 'PP.id_pedido'])
                    ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => post('id_pedido')])
                    ->andWhere(ELOQUENT_EQUAL, ['PP.id_pedido_produto' => post('id_pedido_produto')])
                    ->one();

                $valor_unico = Caster::toFloat(post('valor_unico'));
                $porcentagem_desconto = Caster::toFloat(post('porcentagem_desconto'));
                $quantidade = Caster::toFloat(post('quantidade'));

                if ($model) {

                    if (($quantidade - $model->quantidade) > $model->extra()->quantidade_estoque) {
                        if (!($quantidade < $model->quantidade)) {
                            $recalc = $this->recalc();
                            return [
                                'status' => true,
                                'error' => 'A quantidade inserida é maior que a quantidade em estoque, impossível alterar o pedido.',
                                'quantidade_total' => $recalc['quantidade_total'],
                                'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                                'media_desconto' => $recalc['media_desconto'],
                                'quantidade' => $model->quantidade,
                                'valor_unico' => Formater::intToCurrency($model->valor_unico),
                                'valor_final' => Formater::intToCurrency(($model->valor_unico - ($model->valor_unico * $model->porcentagem_desconto / 100)) * $model->quantidade),
                                'porcentagem_desconto' => $model->porcentagem_desconto,
                            ];
                        }
                    }

                    if ($valor_unico < $model->extra()->valor_minimo) {
                        $recalc = $this->recalc();
                        return [
                            'status' => true,
                            'error' => 'O valor unitário final não pode ser menor que o valor mínimo.',
                            'quantidade_total' => $recalc['quantidade_total'],
                            'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                            'media_desconto' => $recalc['media_desconto'],
                            'quantidade' => $model->quantidade,
                            'valor_unico' => Formater::intToCurrency($model->valor_unico),
                            'valor_final' => Formater::intToCurrency(($model->valor_unico - ($model->valor_unico * $model->porcentagem_desconto / 100)) * $model->quantidade),
                            'porcentagem_desconto' => $model->porcentagem_desconto,
                        ];
                    }

                    if ($valor_unico - ($valor_unico * $porcentagem_desconto / 100) < $model->extra()->valor_minimo) {
                        $recalc = $this->recalc();
                        return [
                            'status' => true,
                            'error' => 'A porcentagem de desconto inserida, faz com que o valor unitário fique menor que o valor mínimo do produto.',
                            'quantidade_total' => $recalc['quantidade_total'],
                            'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                            'media_desconto' => $recalc['media_desconto'],
                            'quantidade' => $model->quantidade,
                            'valor_unico' => Formater::intToCurrency($model->valor_unico),
                            'valor_final' => Formater::intToCurrency(($model->valor_unico - ($model->valor_unico * $model->porcentagem_desconto / 100)) * $model->quantidade),
                            'porcentagem_desconto' => $model->porcentagem_desconto,
                        ];
                    }

                    $status = (new PedidosProdutos())->update(['PP' => PedidosProdutos::tableName()])
                        ->set([
                            'PP.quantidade' => $quantidade,
                            'PP.valor_unico' => $valor_unico,
                            'PP.porcentagem_desconto' => $porcentagem_desconto == 0 ? '0' : $porcentagem_desconto,
                        ])
                        ->where(ELOQUENT_EQUAL, ['PP.id_pedido_produto' => post('id_pedido_produto')])
                        ->execute();

                    $recalc = $this->recalc();

                    if ($status && $model->extra()->innf != 1) {
                        if ($quantidade > $model->quantidade) {
                            $remove = $quantidade - $model->quantidade;
                            (new Produtos())->update(['PR' => Produtos::tableName()])
                                ->set([
                                    'quantidade_estoque' => $model->extra()->quantidade_estoque - $remove,
                                ])
                                ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $model->id_produto])
                                ->execute();
                        } else {
                            $add = $model->quantidade - $quantidade;
                            (new Produtos())->update(['PR' => Produtos::tableName()])
                                ->set([
                                    'quantidade_estoque' => $model->extra()->quantidade_estoque + $add,
                                ])
                                ->where(ELOQUENT_EQUAL, ['PR.id_produto' => $model->id_produto])
                                ->execute();
                        }
                    }

                    return [
                        'status' => $status,
                        'quantidade_total' => $recalc['quantidade_total'],
                        'valor_total' => Formater::intToCurrency($recalc['valor_total']),
                        'media_desconto' => $recalc['media_desconto'],
                        'quantidade' => $quantidade,
                        'valor_unico' => Formater::intToCurrency($valor_unico),
                        'valor_final' => Formater::intToCurrency(($valor_unico - ($valor_unico * $porcentagem_desconto / 100)) * $quantidade),
                        'porcentagem_desconto' => $porcentagem_desconto,
                    ];
                }
            }

            return [
                'status' => false,
                'error' => $this->error_st,
                'message' => $this->message
            ];


        }
        return ['status' => false];
    }

    public function actionProdutos()
    {
        if (is_post()) {
            $get = (new Produtos())
                ->select(['PRODUTOS.nome', 'PRODUTOS.id_produto', 'PRODUTOS.cod_barras'])
                ->from(['PRODUTOS' => 'produtos'])
                ->leftJoin(ELOQUENT_EQUAL, ['CATEGORIAS' => 'produtos_categorias', 'CATEGORIAS.id_categoria' => 'PRODUTOS.id_categoria'])
                ->leftJoin(ELOQUENT_EQUAL, ['SUBCATEGORIAS' => 'produtos_subcategorias', 'SUBCATEGORIAS.id_subcategoria' => 'PRODUTOS.id_subcategoria'])
                ->where(ELOQUENT_LIKE, ['LCASE(PRODUTOS.nome)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(CATEGORIAS.nome)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->orWhere(ELOQUENT_LIKE, ['LCASE(SUBCATEGORIAS.nome)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
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
                return ['data' => $data];
            }
            return ['data' => []];
        }
    }

    public function actionPage()
    {
        AdminAssets::init($this);
        $this->appendJs('tokenstags', Winged::$parent . 'assets/js/pages/tokenstags.js');
        $this->appendJs('numeric', Winged::$parent . 'assets/js/pages/numeric.js');
        $this->appendJs('pedidos', Winged::$parent . 'assets/js/pages/pedidos.js');
        $this->setNicknamesToUri(['page']);
        $limit = get('limit') ? get('limit') : 10;
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
        $model->select(['PEDIDOS.*', 'CLIENTES.nome' => 'cliente', 'CLIENTES.nome_fantasia', 'USUARIOS.nome' => 'usuario', 'CLIENTES.razao_social'])
            ->from(['PEDIDOS' => 'pedidos'])
            ->leftJoin(ELOQUENT_EQUAL, ['CLIENTES' => 'clientes', 'CLIENTES.id_cliente' => 'PEDIDOS.id_cliente'])
            ->leftJoin(ELOQUENT_EQUAL, ['USUARIOS' => 'usuarios', 'USUARIOS.id_usuario' => 'PEDIDOS.id_usuario'])
            ->orderBy(ELOQUENT_DESC, 'PEDIDOS.id_pedido');

        $filter = false;
        //if (!Login::permissionAdm()) {
        //    $model->where(ELOQUENT_EQUAL, ['PEDIDOS.id_usuario' => Login::current()->primaryKey()]);
        //    $filter = true;
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
        ], false);

        \Admin::buildOrderModel($model, [
            'sort_nome' => 'CLIENTES.nome',
            'sort_usuario' => 'USUARIOS.nome',
            'sort_endereco' => 'PEDIDOS.endereco',
            'sort_valor_frete' => 'PEDIDOS.valor_frete',
            'sort_status' => 'PEDIDOS.status',
            'sort_data_cadastro' => 'PEDIDOS.data_cadastro',
            'sort_agendamento' => 'PEDIDOS.agendamento',
        ], true);

        $paginate = new Paginate($model->count(), $model);

        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links, Winged::$page_surname);

        //pre_clear_buffer_die($data->data);

        /*
        $data = sortByKeys($data->data, [
            'sort_nome' => 'cliente',
            'sort_usuario' => '',
            'sort_endereco' => 'PEDIDOS.endereco',
            'sort_valor_frete' => 'PEDIDOS.valor_frete',
            'sort_status' => 'PEDIDOS.status',
            'sort_data_cadastro' => 'PEDIDOS.data_cadastro',
            'sort_agendamento' => 'PEDIDOS.agendamento',
        ]);
        */

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'success' => $success,
            'models' => $data->data,
            'links' => $links,
        ]);
    }
}