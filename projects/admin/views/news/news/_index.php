<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

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
                                <a class="lt-button btn bg-teal-400"
                                   onclick="redirectTo('<?= Admin::buildUrlNoPage($this->insert) ?>', '<?= Admin::buildGetUrl() ?>')">
                                    <span>Adicionar  <i class="icon-plus-circle2"></i></span>
                                </a>
                                <a class="lt-button btn bg-primary-400"
                                   onclick="redirectTo('<?= Admin::buildGetUrl() ?>', '<?= Admin::buildGetUrl() ?>')">
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
                            <form action="<?= Admin::buildGetUrl() ?>" method="get" name="form_list">
                                <div class="dataTables_filter">
                                    <label><span>Busca:</span>
                                        <input type="search" value="<?= get("search"); ?>"
                                               name="search"
                                               placeholder="digite sua busca..."></label>
                                </div>
                                <div class="dt-buttons">
                                    <a class="dt-button btn bg-teal-400"
                                       onclick="redirectTo('<?= Admin::buildUrlNoPage($this->insert) ?>', '<?= Admin::buildGetUrl() ?>')">
                                        <span>Adicionar  <i class=" icon-plus-circle2"></i></span>
                                    </a>
                                </div>
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
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_titulo') ?>', '_self');"
                                        rowspan="1" colspan="1">Título
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_og_title') ?>', '_self');"
                                        rowspan="1" colspan="1">Og:Title Facebook
                                    </th>
                                    <th>
                                        Categorias
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_previa') ?>', '_self');"
                                        rowspan="1" colspan="1">Previa
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_data_cadastro') ?>', '_self');"
                                        rowspan="1" colspan="1">Data cadastro
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_data_alteracao') ?>', '_self');"
                                        rowspan="1" colspan="1">Data alteração
                                    </th>
                                    <th class="sorting"
                                        onclick="window.open('<?= Admin::buildGetUrlSorting('sort_status') ?>', '_self');"
                                        rowspan="1" colspan="1">Status
                                    </th>
                                    <th rowspan="1" colspan="1">Imagem principal</th>
                                    <th rowspan="1" colspan="1">Imagem Facebook</th>
                                    <th rowspan="1" colspan="1" style="width:130px">Editar</th>
                                </tr>
                                </thead>
                                <tbody class="sortable_tr">
                                <?php
                                /**
                                 * @var $models array | \Winged\Model\News[]
                                 * @var $model  \Winged\Model\News
                                 */
                                foreach ($models as $model) {
                                    //$model->categorias = implode(', ', array2htmlselect((new NewsCategorias())->select(['C.*'])
                                    //    ->from(['C' => NewsCategorias::tableName()])
                                    //    ->orderBy(ELOQUENT_ASC, 'C.categoria')
                                    //    ->where(ELOQUENT_IN, ['C.id_categoria' => $model->categorias])
                                    //    ->execute(true), 'categoria', 'id_categoria'));
                                    ?>
                                    <tr data-id="<?= $model->primaryKey() ?>" data-ordem="<?= $model->ordem ?>"
                                        role="row" class="even">
                                        <td><?= $model->titulo ?></td>
                                        <td><?= (new \Winged\Utils\Chord($model->previa))->substrIfNeed(0, 50, true, '...')->get() ?></td>
                                        <td><?= $model->data_cadastro->dmy() ?></td>
                                        <td><?= $model->data_alteracao->dmy() ?></td>
                                        <td>
                                            <span class="label label-<?= $model->status == 0 ? 'danger' : 'success' ?>">
                                                <?= $model->status == 0 ? 'Indisponível' : 'Disponível' ?>
                                            </span>
                                        </td>


                                        <td>
                                            <input type="hidden" name="indice" value="1">
                                            <div class="btn-group">
                                                <button
                                                        onclick="redirectTo('<?= Admin::buildPageNameUrl() ?>update/<?= $model->primaryKey() ?>', '<?= Admin::buildGetUrl() ?>')"
                                                        class="btn bg-orange-400"
                                                        data-popup="tooltip" data-placement="left" title=""
                                                        data-original-title="Editar">
                                                    <i class="icon-pencil"></i>
                                                </button>
                                            </div>
                                            <a href="javascript:;"
                                               onclick="confirmDelete('<?= Admin::buildPageNameUrl() ?>delete/<?= $model->primaryKey() ?>', '<?= Admin::buildGetUrl() ?>')"
                                               class="btn bg-danger" data-placement="top" data-popup="tooltip"
                                               title="" data-original-title="Deletar">
                                                <i class="icon-trash"></i>
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
        </div>
    </div>
<?php
$this->html('_includes/end.content');