<?php

use Winged\Form\ActiveFormPrintable;
use Winged\Database\DbDict;

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
            /**
             * @var $model Model
             */
            if ($model->hasErrors()) {
                ?>
                <div style="margin-top: 10px" class="col-lg-12">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-check"></i> Registro não pode ser salvo</h6>
                        </div>
                        <div class="panel-body">
                            <h6 class="no-margin">
                                <small class="display-block" style="margin-bottom:10px">
                                    Sua última ação de alteração ou inserção não pode ser executada porquê o sistema
                                    encontrou erros ao validar os dados enviados.
                                </small>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php
            }
            $form = new ActiveFormPrintable($model);
            ?>
            <div class="page-container">
                <div class="page-content">
                    <div class="content-wrapper">
                        <div class="content">
                            <div class="row">
                                <?php $form->beggin((Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', []); ?>
                                <?php
                                if ($model->primaryKey()) {
                                    $form->html(Obras::primaryKeyName(), ['document' => 'hidden']);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">Obra</h5>
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php
                                                        $form->html('nome',
                                                            [
                                                                'document' => 'text'
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        );
                                                        ?>

                                                        <div class="col-lg-8 col-md-8">
                                                            <label for="clientes">Está obra pertence ao seguinte
                                                                cliente: </label>
                                                            <input name="id_usuario" id="clientes" type="text"
                                                                   class="form-control">
                                                            <script>var clientes = <?= json_encode($clientes) ?>;</script>
                                                        </div>

                                                        <?php $form->html('arquivo',
                                                            [
                                                                'document' => 'password',
                                                                'value' => ''
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php $form->html('cep',
                                                            [
                                                                'document' => 'text'
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        $options = array2htmlselect((new Estados())
                                                            ->select()
                                                            ->from(['ESTADO' => 'estados'])
                                                            ->orderBy(ELOQUENT_ASC, 'ESTADO.id_estado')
                                                            ->execute(true), 'estado', 'id_estado');
                                                        $options[0] = 'Selecione o estado';
                                                        ksort($options);

                                                        $form->html('estado',
                                                            [
                                                                'document' => 'select',
                                                                'options' => $options,
                                                                'attrs' => [
                                                                    'readonly' => 'readonly'
                                                                ],
                                                                'class' => [
                                                                    'readonly'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        $cidades = (new Cidades())
                                                            ->select()
                                                            ->from(['CIDADES' => 'cidades'])
                                                            ->orderBy(ELOQUENT_ASC, 'CIDADES.id_cidade');

                                                        if ($model->estado > 0) {
                                                            $cidades->where(ELOQUENT_EQUAL, ['CIDADES.id_estado' => $model->estado]);
                                                        }

                                                        $options = array2htmlselect($cidades->execute(true), 'cidade', 'id_cidade');
                                                        $options[0] = 'Selecione a cidade';
                                                        ksort($options);

                                                        $form->html('cidade',
                                                            [
                                                                'document' => 'select',
                                                                'options' => $options,
                                                                'attrs' => [
                                                                    'readonly' => 'readonly'
                                                                ],
                                                                'class' => [
                                                                    'readonly'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        $form->html('rua',
                                                            [
                                                                'document' => 'text',
                                                                'attrs' => [
                                                                    'readonly' => 'readonly'
                                                                ],
                                                                'class' => [
                                                                    'readonly'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php

                                                        $form->html('numero',
                                                            [
                                                                'document' => 'text',
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        );

                                                        $form->html('complemento',
                                                            [
                                                                'document' => 'text',
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        );

                                                        $bairros = (new Bairros())
                                                            ->select()
                                                            ->from(['BAIRROS' => 'bairros'])
                                                            ->orderBy(ELOQUENT_ASC, 'BAIRROS.id_bairro');

                                                        if ($model->estado > 0) {
                                                            $bairros->where(ELOQUENT_EQUAL, ['BAIRROS.id_estado' => $model->estado]);
                                                            if ($model->cidade > 0) {
                                                                $bairros->andWhere(ELOQUENT_EQUAL, ['BAIRROS.id_cidade' => $model->cidade]);
                                                            }
                                                        } else {
                                                            if ($model->cidade > 0) {
                                                                $bairros->where(ELOQUENT_EQUAL, ['BAIRROS.id_cidade' => $model->cidade]);
                                                            }
                                                        }

                                                        $options = array2htmlselect($bairros->execute(true), 'nome', 'id_bairro');
                                                        $options[0] = 'Selecione o bairro';
                                                        ksort($options);

                                                        $form->html('bairro',
                                                            [
                                                                'document' => 'select',
                                                                'options' => $options,
                                                                'attrs' => [
                                                                    'readonly' => 'readonly'
                                                                ],
                                                                'class' => [
                                                                    'readonly'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php
                                                        $form->html('status',
                                                            [
                                                                'document' => 'onoff',
                                                                'value' => 1,
                                                                'attrs' => [
                                                                    'checked' => $model->status == 1 ? 'checked' : '',
                                                                ],
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="file-styled">Documento: </label>
                                                    <?php

                                                    /**
                                                     * @var $this Controller
                                                     */

                                                    if (!empty($model->anexos)) {
                                                        ?>
                                                        <div class="files">
                                                            <p class="help-block text-success-400">
                                                                <i class="glyphicon glyphicon-info-sign"></i>
                                                                Documento atual.
                                                            </p>
                                                            <div style="margin-bottom: 10px;">
                                                                <?php
                                                                if(is_array($model->anexos)){
                                                                    foreach ($model->anexos as $key => $anexo) {
                                                                        $exp = explode('/', $anexo);
                                                                        $exp = end($exp);
                                                                        if ($key === 0) {
                                                                            ?>
                                                                            <span class="badge badge-success"><?= $exp ?>
                                                                                <span data-id="<?= $anexo ?>"
                                                                                      class="caret x"></span></span>
                                                                            <?php
                                                                        } else {
                                                                            ?>
                                                                            <span style="margin-left: 5px;"
                                                                                  class="badge badge-success"><?= $exp ?>
                                                                                <span data-id="<?= $anexo ?>"
                                                                                      class="caret x"></span></span>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        if(is_array($model->anexos)){
                                                            foreach ($model->anexos as $key => $anexo) {
                                                                ?>
                                                                <input data-id="<?= $anexo ?>" type="hidden"
                                                                       name="Obras[keep_files][]" class="keep_files"
                                                                       value="<?= $anexo ?>"/>
                                                                <?php
                                                            }
                                                        }
                                                    } else {
                                                        ?>
                                                        <input type="hidden" name="Obras[keep_files]" value=""/>
                                                        <?php
                                                    }

                                                    ?>
                                                    <div class="uploader" id="uniform-file-styled">
                                                        <input type="file" id="file-styled"
                                                               class="alpaca-control" name="Obras[anexos][]"
                                                               autocomplete="off"/>
                                                    </div>

                                                    <p class="help-block ">
                                                        <i class="glyphicon glyphicon-info-sign"></i>
                                                        Insira um documento em formato .PDF
                                                    </p>
                                                </div>

                                                <legend class="mb-20"></legend>
                                                <div class="text-right mt-20">
                                                    <?php
                                                    $form->html(null,
                                                        [
                                                            'document' => 'bsubmit',
                                                            'selectors' => [
                                                                'button' => [
                                                                    'text' => 'Enviar'
                                                                ]
                                                            ],
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
                                    </div>
                                </div>
                                <?php $form->end(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->html('_includes/end.content');
