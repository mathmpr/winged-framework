<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');


use Winged\Formater\Formater;
use Winged\Date\Date;

?>
    <div class="content-wrapper">
        <div class="page-header page-header-default">
            <div class="page-header-content">
                <div class="page-title">
                    <h4><?= $this->page_name ?></h4>
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
            if ($success) {
                ?>
                <div style="margin-top: 10px" class="col-lg-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-check"></i> Registro salvo
                                com sucesso</h6>
                        </div>
                        <div class="panel-body">
                            <h6 class="no-margin">
                                <small class="display-block" style="margin-bottom:10px">
                                    Sua última ação de alteração ou inserção foi executada com sucesso.
                                </small>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php
            }
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
                </div>
                <?php
            } else {
                ?>
                <div class="panel panel-flat">
                    <div id="DataTables_Table_1_wrapper" class="dataTables_wrapper no-footer">
                        <div class="datatable-header">
                            <form action="<?= \Admin::buildGetUrl() ?>" method="get" name="form_list">
                                <div class="dataTables_filter">
                                    <label><span>Busca:</span>
                                        <input type="search" value="<?= get("search"); ?>"
                                               name="search"
                                               placeholder="digite sua busca...">
                                        <button class="btn bg-blue-400"><i class="icon-search4"></i></button>
                                    </label>
                                </div>
                                <!--div class="dt-buttons">
                                    <a class="dt-button btn bg-teal-400"
                                       onclick="redirectTo('<?= \Admin::buildUrlNoPage($this->insert) ?>', '<?= \Admin::buildGetUrl() ?>')">
                                        <span>Mostrar  <i class=" icon-plus-circle2"></i></span>
                                    </a>
                                </div-->
                                <div class="dataTables_length">
                                    <label>
                                        <span>Mostrar:</span>
                                        <select name="limit" class="select2-hidden-accessible">
                                            <option
                                                    value="10" <?= (get('limit') == 10) ? 'selected="selected"' : ''; ?>>
                                                10
                                            </option>
                                            <option
                                                    value="25" <?= (get('limit') == 25) ? 'selected="selected"' : ''; ?>>
                                                25
                                            </option>
                                            <option
                                                    value="50" <?= (get('limit') == 50) ? 'selected="selected"' : ''; ?>>
                                                50
                                            </option>
                                            <option
                                                    value="100" <?= (get('limit') == 100) ? 'selected="selected"' : ''; ?>>
                                                100
                                            </option>
                                        </select>
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
                                 * @var $models array | Pedidos[]
                                 * @var $model Pedidos
                                 */

                                foreach ($models as $model) {


                                    $quantidade = 0;

                                    $valor = 0;

                                    $desconto = 0;

                                    $produtos = (new PedidosProdutos())
                                        ->select()
                                        ->from(['PP' => PedidosProdutos::tableName()])
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
                                        }
                                    }

                                    $valor += $model->valor_frete;


                                    $style = $model->innf == '0' ? '' : ' style="background: rgb(0, 230, 248); color: #fff;"';

                                    ?>
                                    <tr <?= $style ?> data-status="<?= $model->status ?>" data-id="<?= $model->primaryKey() ?>"
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
                                        <th style="display: none" colspan="2">Porcentagem de desconto</th>
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
                                                <td style="display: none;" colspan="2">
                                                    <div style="margin: 0" class="form-group">
                                                        <input field-name="Porcentagem de desconto"
                                                               name="porcentagem_desconto"
                                                               class="porcentagem_desconto form-control"
                                                               data-old="<?= $produto->porcentagem_desconto ?>"
                                                               value="<?= $produto->porcentagem_desconto ?>"/>
                                                    </div>
                                                </td>
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
                                                <input name="produto"
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
                        <div class="datatable-footer">
                            <div class="dataTables_info"></div>
                            <div class="dataTables_paginate paging_simple_numbers">
                                <?= $links ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>

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
                                                    <input name="innf" id="innf0" value="0" type="radio"><label for="innf0">
                                                        <span></span>Não
                                                    </label>
                                                </div>
                                                <div style="display: inline-block; margin-left: 30px;">
                                                    <input name="innf" id="innf1" value="1" type="radio"><label for="innf1">
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