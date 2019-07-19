<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Form\Form;
use Winged\Model\Model;
use Winged\Model\Produtos;
use Winged\Database\DbDict;
use Winged\Formater\Formater;
use Winged\Date\Date;

?>
    <div class="content-wrapper">
        <div class="page-header page-header-default">
            <div class="page-header-content">
                <div class="page-title">
                    <h4><?= $this->page_name ?></h4>
                    <small>
                        O relatório e estatísticas são gerados de acordo com os dados passados para sistema na tela
                        anterior e buscas feitas nessa mesma tela.
                    </small>
                </div>
            </div>
            <div class="breadcrumb-line">
                <ul class="breadcrumb">
                    <li class="active"><?= $this->page_action_string ?></li>
                </ul>
            </div>
        </div>
        <div class="content">
            <?php
            if (!$models) {
                ?>
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-cross2"></i> Não há registros cadastrados para
                                essa sessão ou busca</h6>
                        </div>
                        <div class="panel-body">
                            <h6 class="no-margin">
                                <small class="display-block" style="margin-bottom:10px">Nenhum registro foi
                                    encontrado para a consulta/sessão!
                                </small>
                            </h6>
                            <div class="lt-buttons">
                                <a class="lt-button btn bg-primary-400"
                                   onclick="redirectTo('<?= \Admin::buildGetUrl() ?>', '<?= \Admin::buildGetUrl() ?>')">
                                    <span>Atualizar  <i class="icon-reset"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-flat">

                        <?php
                        $form = new Form($nmodel);
                        echo $form->begin((\Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true);
                        ?>

                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('data_inicial', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('data_final', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('clientes', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('produtos', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('bairros', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('status', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>

                                <legend class="mb-20"></legend>
                                <div class="text-right mt-20">
                                    <?=
                                    $form->addInput(null, 'Button',
                                        [
                                            'text' => 'Enviar',
                                            'class' => [
                                                'btn',
                                                'bg-primary-400',
                                                'mt-20'
                                            ],
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>

                        <?php
                        $form->end();
                        ?>

                        <script>var clientes = <?= json_encode($nmodel->clientes) ?></script>
                        <script>var bairros = <?= json_encode($nmodel->bairros) ?></script>
                        <script>var produtos = <?= json_encode($nmodel->produtos) ?></script>
                        <script>var _status = <?= json_encode($nmodel->status) ?></script>

                        <?php

                        $nmodel->clientes = '';
                        $nmodel->bairros = '';
                        $nmodel->produtos = '';
                        $nmodel->status = '';

                        ?>

                    </div>

                </div>
                <?php
            } else {
                ?>

                <div class="panel panel-flat panel-collapsed">

                    <div class="panel-heading">
                        <h5 style="color: #00a2ff; cursor: pointer;" class="uncollapse panel-title">Editar filtros e
                            refazer pesquisa</h5>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>

                    <?php
                    $form = new Form($nmodel);
                    echo $form->begin((\Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true);
                    ?>

                    <div class="panel-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="row">
                                    <?=
                                    $form->addInput('data_inicial', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-6']
                                        ]
                                    );
                                    ?>
                                    <?=
                                    $form->addInput('data_final', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-6']
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <?=
                                    $form->addInput('clientes', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-12']
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <?=
                                    $form->addInput('produtos', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-12']
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <?=
                                    $form->addInput('bairros', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-12']
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <?=
                                    $form->addInput('status', 'Input',
                                        [],
                                        [
                                            'class' => ['col-md-12']
                                        ]
                                    );
                                    ?>
                                </div>
                            </div>

                            <legend class="mb-20"></legend>
                            <div class="text-right mt-20">
                                <?=
                                $form->addInput(null, 'Button',
                                    [
                                        'text' => 'Enviar',
                                        'class' => [
                                            'btn',
                                            'bg-primary-400',
                                            'mt-20'
                                        ],
                                    ]
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $form->end();
                    ?>

                    <script>var clientes = <?= json_encode($nmodel->clientes) ?></script>
                    <script>var bairros = <?= json_encode($nmodel->bairros) ?></script>
                    <script>var produtos = <?= json_encode($nmodel->produtos) ?></script>
                    <script>var _status = <?= json_encode($nmodel->status) ?></script>

                    <?php

                    $nmodel->clientes = '';
                    $nmodel->bairros = '';
                    $nmodel->produtos = '';
                    $nmodel->status = '';

                    ?>

                </div>

                <div style="background-color: transparent; border: none; -webkit-box-shadow: none; box-shadow: none;"
                     class="panel panel-flat">
                    <div class="row">

                        <div class="col-md-4">
                            <div style="margin-bottom: 0px;" class="panel panel-flat">
                                <div class="panel-heading">
                                    <h5 class="panel-title"><span class="text-highlight bg-teal-400">Lucro total de todo o período</span><a
                                                class="heading-elements-toggle"><i class="icon-more"></i></a></h5>
                                </div>

                                <div id="full_lucro" class="panel-body">
                                    <h5></h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div style="margin-bottom: 0px;" class="panel panel-flat">
                                <div class="panel-heading">
                                    <h5 class="panel-title"><span class="text-highlight bg-primary">Valor total de todo o período</span><a
                                                class="heading-elements-toggle"><i class="icon-more"></i></a></h5>
                                </div>

                                <div id="full_valor" class="panel-body">
                                    <h5></h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div style="margin-bottom: 0px;" class="panel panel-flat">
                                <div class="panel-heading">
                                    <h5 class="panel-title"><span class="text-highlight bg-success">Quantidade total de todo o período</span><a
                                                class="heading-elements-toggle"><i class="icon-more"></i></a></h5>
                                </div>

                                <div id="full_quantidade" class="panel-body">
                                    <h5></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-flat panel-collapsed">

                    <div class="panel-heading">
                        <h5 style="color: #00a2ff; cursor: pointer;" class="uncollapse panel-title">Resumo total dos
                            produtos ordenado por valor</h5>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #ddd;" class="dataTables_wrapper no-footer">
                        <div class="datatable-scroll-wrap">
                            <table id="resumo_produtos_valor" class="table table-bordered no-footer dataTable">
                                <thead>
                                <tr role="row">
                                    <th rowspan="1" colspan="1">Produto
                                    </th>
                                    <th rowspan="1" colspan="1">Quantidade
                                    </th>
                                    <th rowspan="1" colspan="1">Valor
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-flat panel-collapsed">
                    <div class="panel-heading">
                        <h5 style="color: #00a2ff; cursor: pointer;" class="uncollapse panel-title">Resumo total dos
                            produtos ordenado por quantidade</h5>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #ddd;" class="dataTables_wrapper no-footer">
                        <div class="datatable-scroll-wrap">
                            <table id="resumo_produtos_quantidade" class="table table-bordered no-footer dataTable">
                                <thead>
                                <tr role="row">
                                    <th rowspan="1" colspan="1">Produto
                                    </th>
                                    <th rowspan="1" colspan="1">Quantidade
                                    </th>
                                    <th rowspan="1" colspan="1">Valor
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-flat panel-collapsed">
                    <div class="panel-heading">
                        <h5 style="color: #00a2ff; cursor: pointer;" class="uncollapse panel-title">Resumo total dos
                            vendedores ordenado por valor</h5>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #ddd;" class="dataTables_wrapper no-footer">
                        <div class="datatable-scroll-wrap">
                            <table id="resumo_vendedores" class="table table-bordered no-footer dataTable">
                                <thead>
                                <tr role="row">
                                    <th rowspan="1" colspan="1">Vendedor
                                    </th>
                                    <th rowspan="1" colspan="1">Quantidade
                                    </th>
                                    <th rowspan="1" colspan="1">Valor
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-flat panel-collapsed">
                    <div class="panel-heading">
                        <h5 style="color: #00a2ff; cursor: pointer;" class="uncollapse panel-title">Resumo total dos
                            clientes ordenado por valor</h5>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #ddd;" class="dataTables_wrapper no-footer">
                        <div class="datatable-scroll-wrap">
                            <table id="resumo_clientes" class="table table-bordered no-footer dataTable">
                                <thead>
                                <tr role="row">
                                    <th rowspan="1" colspan="1">Cliente
                                    </th>
                                    <th rowspan="1" colspan="1">Quantidade
                                    </th>
                                    <th rowspan="1" colspan="1">Valor
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-flat">
                    <div id="DataTables_Table_1_wrapper" class="dataTables_wrapper no-footer">
                        <div class="datatable-header">
                            <form action="<?= \Admin::buildGetUrl() ?>" method="get" name="form_list">
                                <div class="dataTables_filter">
                                    <label><span>Busca:</span>
                                        <input type="search" value="<?= get("search"); ?>"
                                               name="search"
                                               placeholder="digite sua busca...">
                                        <button class="btn bg-blue-400">
                                            <i class="icon-search4"></i>
                                        </button>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="datatable-scroll-wrap">
                            <table class="table table-bordered no-footer dataTable">
                                <thead>
                                <tr role="row">
                                    <th>Detalhes</th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_id_pedido') ?>', '_self');"
                                        rowspan="1" colspan="1">Nº Pedido
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_nome') ?>', '_self');"
                                        rowspan="1" colspan="1">Cliente
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_usuario') ?>', '_self');"
                                        rowspan="1" colspan="1">Vendedor
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_endereco') ?>', '_self');"
                                        rowspan="1" colspan="1">Endereço para entrega
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_valor_frete') ?>', '_self');"
                                        rowspan="1" colspan="1">Nome fantasia
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_status') ?>', '_self');"
                                        rowspan="1" colspan="1">Status
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_data_cadastro') ?>', '_self');"
                                        rowspan="1" colspan="1">Data em que foi feito
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= \Admin::buildGetUrlSorting('sort_agendamento') ?>', '_self');"
                                        rowspan="1" colspan="1">Entrega agendada para
                                    </th>
                                    <th rowspan="1" colspan="1">Quantidade total
                                    </th>
                                    <th rowspan="1" colspan="1">Valor total
                                    </th>
                                    <!--th rowspan="1" colspan="1">Média de desconto
                                    </th-->
                                    <th rowspan="1" colspan="1" style="width:175px; text-align: center;">Editar</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                /**
                                 * @var $models array | \Winged\Model\Pedidos[]
                                 * @var $model \Winged\Model\RelatorioVendas
                                 */


                                $tojs = [
                                    'valor' => 0,
                                    'quantidade' => 0,
                                    'custo' => 0,
                                    'sellers' => [

                                    ],
                                    'acheters' => [

                                    ],
                                    'produtos' => [

                                    ],
                                ];

                                foreach ($models as $model) {


                                    $quantidade = 0;

                                    $valor = 0;

                                    $custo = 0;

                                    $desconto = 0;

                                    $produtos = (new PedidosProdutos())
                                        ->select()
                                        ->from(['PP' => PedidosProdutos::tableName()])
                                        ->leftJoin(['P' => Produtos::tableName()], 'PP.id_produto = P.id_produto')
                                        ->where(ELOQUENT_EQUAL, ['PP.id_pedido' => $model->primaryKey()])
                                        ->execute();

                                    /**
                                     * @var $produto PedidosProdutos
                                     */

                                    if ($produtos) {
                                        foreach ($produtos as $produto) {
                                            $quantidade += $produto->quantidade;
                                            $valor += ($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade;
                                            $desconto += $produto->porcentagem_desconto;
                                            $custo += $produto->fix_custo * $produto->quantidade;

                                            if (!array_key_exists($produto->id_produto, $tojs['produtos'])) {
                                                $tojs['produtos'][$produto->id_produto] = [
                                                    'nome' => $produto->nome,
                                                    'quantidade' => 0,
                                                    'valor' => 0,
                                                ];
                                            }

                                            $tojs['produtos'][$produto->id_produto]['quantidade'] += $produto->quantidade;
                                            $tojs['produtos'][$produto->id_produto]['valor'] += ($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade;
                                            $desconto += $produto->porcentagem_desconto;

                                            if (!array_key_exists($model->id_cliente, $tojs['acheters'])) {
                                                $tojs['acheters'][$model->id_cliente] = [
                                                    'nome' => $model->extra()->cliente != '' ? $model->extra()->cliente . ' - ' . $model->extra()->razao_social : 'Sem nome - ' . $model->extra()->razao_social,
                                                    'quantidade' => 0,
                                                    'valor' => 0,
                                                ];
                                            }

                                            $tojs['acheters'][$model->id_cliente]['quantidade'] += $produto->quantidade;
                                            $tojs['acheters'][$model->id_cliente]['valor'] += ($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade;


                                            if (!array_key_exists($model->id_usuario, $tojs['sellers'])) {
                                                $tojs['sellers'][$model->id_usuario] = [
                                                    'nome' => $model->extra()->usuario,
                                                    'quantidade' => 0,
                                                    'valor' => 0,
                                                ];
                                            }

                                            $tojs['sellers'][$model->id_usuario]['quantidade'] += $produto->quantidade;
                                            $tojs['sellers'][$model->id_usuario]['valor'] += (($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade);

                                        }
                                    }

                                    $valor += $model->valor_frete;

                                    $tojs['sellers'][$model->id_usuario]['valor'] += $model->valor_frete;

                                    $tojs['valor'] += $valor;
                                    $tojs['custo'] += $custo;
                                    $tojs['quantidade'] += $quantidade;

                                    $style = $model->innf == '0' ? '' : ' style="background: rgb(0, 230, 248); color: #fff;"';

                                    ?>
                                    <tr <?= $style ?> data-status="<?= $model->status ?>"
                                                      data-id="<?= $model->primaryKey() ?>"
                                                      role="row" class="main even">
                                        <td style="text-align: center;">
                                            <input name="agendamento" value="<?= $model->agendamento ?>" type="hidden"/>
                                            <input name="innf" value="<?= $model->innf ?>" type="hidden"/>
                                            <input name="metodo_pagamento" value="<?= $model->metodo_pagamento ?>"
                                                   type="hidden"/>
                                            <input name="observacoes" value="<?= brtonl($model->observacoes) ?>"
                                                   type="hidden"/>
                                            <input name="valor_frete"
                                                   value="<?= Formater::intToCurrency($model->valor_frete) ?>"
                                                   type="hidden"/>
                                            <button class="details btn bg-blue-400"><i class="icon-plus2"></i></button>
                                        </td>
                                        <td><?= $model->primaryKey() ?></td>
                                        <td><?= $model->extra()->cliente != '' ? $model->extra()->cliente . ' - ' . $model->extra()->razao_social : 'Sem nome - ' . $model->extra()->razao_social ?></td>
                                        <td><?= $model->extra()->usuario ?></td>
                                        <td><?= $model->endereco ?></td>
                                        <!--td class="valor_frete">R$ <?= Formater::intToCurrency($model->valor_frete) ?></td-->
                                        <td><?= $model->extra()->nome_fantasia ?></td>
                                        <td style="text-align: center">
                                            <span class="label label-<?= $model->getStatusColor(); ?>">
                                                <?= $model->getStatus(); ?>
                                            </span>
                                        </td>
                                        <td><?= $model->data_cadastro ?></td>
                                        <td class="agendamento"><?= $model->agendamento ?></td>
                                        <td class="quantidade_total"><?= $quantidade ?></td>
                                        <td class="valor_total">R$ <?= Formater::intToCurrency($valor) ?></td>
                                        <!--td class="media_desconto"><?= number_format($desconto / count($produtos), 2) ?></td!-->
                                        <td>
                                            <div class="btn-group">
                                                <button <?= $model->status == 4 || $model->status == 3 ? 'style="opacity: 0.4"' : '' ?>
                                                        class="<?= $model->status == 4 || $model->status == 3 ? '' : 'edit-pedido' ?> btn bg-orange-400"
                                                        data-popup="tooltip" data-placement="left" title=""
                                                        data-original-title="<?= $model->status != 4 || $model->status != 3 ? 'Editar' : 'Possivel alterar somente pedidos em aberto' ?>">
                                                    <i class="icon-pencil"></i>
                                                </button>
                                            </div>
                                            <a <?= $model->status != 4 ? 'style="opacity: 0.4"' : '' ?>
                                                    href="javascript:;"
                                                <?= $model->status == 4 ? 'onclick="confirmDelete(\'' . \Admin::buildPageNameUrl() . 'delete/' . $model->primaryKey() . '\', \'' . \Admin::buildGetUrl() . '\')"' : '' ?>
                                                    class="btn bg-danger" data-placement="top" data-popup="tooltip"
                                                    title=""
                                                    data-original-title="<?= $model->status == 4 ? 'Deletar' : 'Possível deletar somente se o pedido estiver cancelado' ?>">
                                                <i class="icon-trash"></i>
                                            </a>
                                            <a href="javascript:;"
                                               onclick="redirectTo('<?= \Admin::buildPageNameUrl('pedidos') ?>print/<?= (new Date($model->data_cadastro))->timestamp() ?>_<?= $model->primaryKey() ?>', '<?= \Admin::buildGetUrl() ?>', this, true)"
                                               class="btn bg-blue-400" data-placement="top" data-popup="tooltip"
                                               title=""
                                               data-original-title="Imprimir" target="_blank">
                                                <i class="icon-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="display: none; background: #f1f1f1" data-id="<?= $model->primaryKey() ?>"
                                        role="row" class="even minus">
                                        <th></th>
                                        <th colspan="4">Nome do produto</th>
                                        <th colspan="2">Quantidade do produto</th>
                                        <th colspan="2">Valor unitário</th>
                                        <!--th colspan="2">Porcentagem de desconto</th-->
                                        <th colspan="2">Valor total</th>
                                        <th style="text-align: center">Remover</th>
                                    </tr>
                                    <?php

                                    if ($produtos) {
                                        foreach ($produtos as $produto) {
                                            ?>
                                            <tr style="display: none; background: #f1f1f1"
                                                data-id-pedido-produto="<?= $produto->primaryKey() ?>"
                                                data-id="<?= $model->primaryKey() ?>" role="row" class="even minus">
                                                <td></td>
                                                <td colspan="4"><?= $produto->nome ?></td>
                                                <td colspan="2">
                                                    <div style="margin: 0" class="form-group">
                                                        <input field-name="Quantidade" name="quantidade"
                                                               class="quantidade form-control"
                                                               data-old="<?= $produto->quantidade ?>"
                                                               value="<?= $produto->quantidade ?>"/>
                                                    </div>
                                                </td>
                                                <td colspan="2">
                                                    <div style="margin: 0" class="form-group">
                                                        <input field-name="Valor unitário" name="valor_unico"
                                                               class="valor_unico form-control"
                                                               data-old="<?= Formater::intToCurrency($produto->valor_unico) ?>"
                                                               value="<?= Formater::intToCurrency($produto->valor_unico) ?>"/>
                                                    </div>
                                                </td>
                                                <!--td colspan="2">
                                                    <div style="margin: 0" class="form-group">
                                                        <input field-name="Porcentagem de desconto"
                                                               name="porcentagem_desconto"
                                                               class="porcentagem_desconto form-control"
                                                               data-old="<?= $produto->porcentagem_desconto ?>"
                                                               value="<?= $produto->porcentagem_desconto ?>"/>
                                                    </div>
                                                </td-->
                                                <td colspan="2" class="valor_final">
                                                    R$ <?= Formater::intToCurrency(($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade) ?></td>
                                                <td style="text-align: center;">
                                                    <a href="javascript:;" class="remove-unic btn bg-danger"
                                                       data-placement="top" data-popup="tooltip"
                                                       title=""
                                                       data-original-title="Deletar este item">
                                                        <i class="icon-cross2"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }

                                    ?>

                                    <tr style="display: none; background: #f1f1f1" data-id="<?= $model->primaryKey() ?>"
                                        role="row" class="even minus">
                                        <td></td>
                                        <td colspan="10">
                                            <div style="margin: 0" class="form-group">
                                                <input field-name="Porcentagem de desconto"
                                                       name="produto"
                                                       class="form-control" placeholder="Nome do produto"/>
                                            </div>
                                        </td>
                                        <td style="text-align: center">
                                            <a href="javascript:;" class="addnew btn bg-blue-400"
                                               data-placement="top" data-popup="tooltip"
                                               title=""
                                               data-original-title="Adicicionar novo produto">
                                                <i class="icon-plus2"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
            }


            $sort_valor = [];

            foreach ($tojs['produtos'] as $key => $produto) {
                $sort_valor[$key] = $produto['valor'];
            }

            arsort($sort_valor);

            $produtos_valor = [];

            foreach ($sort_valor as $s => $so) {
                $tojs['produtos'][$s]['valor'] = Formater::intToCurrency($tojs['produtos'][$s]['valor']);
                $produtos_valor[$s . 'str'] = $tojs['produtos'][$s];
            }

            ///

            $sort_quantidade = [];

            foreach ($tojs['produtos'] as $key => $produto) {
                $sort_quantidade[$key] = $produto['quantidade'];
            }

            arsort($sort_quantidade);

            $produtos_quantidade = [];

            foreach ($sort_quantidade as $s => $so) {
                $produtos_quantidade[$s . 'str'] = $tojs['produtos'][$s];
            }

            ///

            $sort_acheters = [];

            foreach ($tojs['acheters'] as $key => $produto) {
                $sort_acheters[$key] = $produto['valor'];
            }

            arsort($sort_acheters);

            $acheters_valor = [];

            foreach ($sort_acheters as $s => $so) {
                $tojs['acheters'][$s]['valor'] = Formater::intToCurrency($tojs['acheters'][$s]['valor']);
                $acheters_valor[$s . 'str'] = $tojs['acheters'][$s];
            }

            ///

            $sort_sellers = [];

            foreach ($tojs['sellers'] as $key => $produto) {
                $sort_sellers[$key] = $produto['valor'];
            }

            arsort($sort_sellers);

            $sellers_valor = [];

            foreach ($sort_sellers as $s => $so) {
                $tojs['sellers'][$s]['valor'] = Formater::intToCurrency($tojs['sellers'][$s]['valor']);
                $sellers_valor[$s . 'str'] = $tojs['sellers'][$s];
            }

            ///

            $tojs['acheters'] = $acheters_valor;

            $tojs['sellers'] = $sellers_valor;

            $tojs['produtos_valor'] = $produtos_valor;

            $tojs['produtos_quantidade'] = $produtos_quantidade;

            $tojs['custo'] = Formater::intToCurrency($tojs['valor'] - $tojs['custo']);

            $tojs['valor'] = Formater::intToCurrency($tojs['valor']);

            $tojs['inicial'] = (new Date($nmodel->data_inicial))->custom('%A dia %d de %B de %Y', true, ['dia', 'de']);

            $tojs['final'] = (new Date($nmodel->data_final))->custom('%A dia %d de %B de %Y', true, ['dia', 'de']);

            ?>

            <script>
                var tojs = <?= json_encode($tojs) ?>;
            </script>

            <button id="comfirm-update" style="display: none" type="button" data-toggle="modal"
                    data-target="#modal-update"></button>
            <div id="modal-update" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form>
                            <input type="hidden" id="id_pedido" name="id_pedido" value=""/>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">×</button>
                                <h5 class="modal-title">Alterar pedido</h5>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning alert-styled-left text-slate-800 content-group">
                                    <span class="text-semibold">Alterar pedido</span>
                                    <button type="button" class="close" data-dismiss="alert">Ã—</button>
                                </div>
                                <p><strong>Obs:</strong> Existem algumas regras lógicas para os status do pedido: </p>
                                <p>Um pedido entregue não pode ter seu status alterado;</p>
                                <p>Um pedido cancelado não pode ter seu status alterado;</p>
                                <br>
                                <div class="row">
                                    <div class="form-group clearfix">
                                        <div class="col-lg-6">
                                            <label for="agendamento">Entrega agendada para: </label>
                                            <input name="agendamento" id="agendamento" type="text"
                                                   class="form-control"/>
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="metodo_pagamento">Método de pagamento: </label>
                                            <input name="metodo_pagamento" id="metodo_pagamento" type="text"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-lg-12">
                                            <label for="valor_frete">Valor frete: </label>
                                            <input name="valor_frete" id="valor_frete" type="text"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-lg-12">
                                            <label for="agendamento">Pedido saiu com NF: </label>
                                            <div class="radio">
                                                <div style="display: inline-block;">
                                                    <input name="innf" id="innf0" value="0" type="radio"><label
                                                            for="innf0">
                                                        <span></span>Não
                                                    </label>
                                                </div>
                                                <div style="display: inline-block; margin-left: 30px;">
                                                    <input name="innf" id="innf1" value="1" type="radio"><label
                                                            for="innf1">
                                                        <span></span>Sim
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-lg-12">
                                            <label for="agendamento">Observações: </label>
                                            <textarea style="height: 100px;" class="form-control" id="observacoes"
                                                      name="observacoes"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <div class="col-lg-12">
                                            <label for="status">Status </label>
                                            <select name="status" id="status" type="text"
                                                    class="form-control">
                                                <option value="">Selecione o novo estatus do pedido</option>
                                                <option value="1">Aguardando separação</option>
                                                <option value="2">Encaminhado</option>
                                                <option value="3">Entregue</option>
                                                <option value="4">Cancelado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-danger-400" data-dismiss="modal"><i
                                            class="icon-cross"></i> Fechar
                                </button>
                                <button type="button" id="render-update" class="btn bg-blue-400"><i
                                            class="icon-check" data-dismiss="modal"></i> Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <button id="comfirm-delete" style="display: none" type="button" data-toggle="modal"
                    data-target="#modal-delete"></button>
            <div id="modal-delete" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h5 class="modal-title">Deletar registro</h5>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert-styled-left text-slate-800 content-group">
                                <span class="text-semibold">Deletar registro</span> Essa ação é irreversível
                                <button type="button" class="close" data-dismiss="alert">Ã—</button>
                            </div>
                            <p>Caso esse registro esteja ligado a outros registros de outras partes do sistema, os mesmo
                                serão deletados ao executar essa ação. Você deseja mesmo realizar essa ação? </p>
                            <hr>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-link" data-dismiss="modal"><i class="icon-enter5"></i> Cancelar
                            </button>
                            <button id="render-delete" class="btn btn-danger"><i class="icon-cross"></i> Deletar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button id="comfirm-remove" style="display: none" type="button" data-toggle="modal"
                    data-target="#modal-remove"></button>
            <div id="modal-remove" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h5 class="modal-title">Deletar item do pedido</h5>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-danger alert-styled-left text-slate-800 content-group">
                                <span class="text-semibold">Deletar item</span> Confirmar exclusão do item
                                <button type="button" class="close" data-dismiss="alert">Ã—</button>
                            </div>
                            <p>Essa ação é irreversível. Caso confirme essa ação e deseje voltar atrás, vá até a última
                                linha desse pedido nessa mesma tela, selecione um produto e clique no botão azul com
                                ícone de mais ao lado.</p>
                            <p>Se o pedido ficar vazio ele sera deletado e sumirá dos registros.</p>
                            <hr>
                        </div>
                        <div class="modal-footer">
                            <button class="btn bg-blue-400" data-dismiss="modal"><i class="icon-check"></i> Cancelar
                            </button>
                            <button id="remove-item" class="btn bg-danger-400" data-dismiss="modal"><i
                                        class="icon-cross2"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->html('_includes/end.content');