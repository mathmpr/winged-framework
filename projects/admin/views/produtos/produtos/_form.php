<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Form\Form;
use Winged\Database\DbDict;
use Winged\Model\Produtos;
use Winged\Model\Estados;
use Winged\Http\Session;
use Winged\Model\ProdutosSubcategorias;
use Winged\Model\Login;

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

            $form = new Form($model);

            ?>
            <div class="page-container">
                <div class="page-content">
                    <div class="content-wrapper">
                        <div class="content">
                            <div class="row">
                                <?= $form->begin((\Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true); ?>
                                <?php
                                if ($model->primaryKey()) {
                                    echo $form->addInput(Produtos::primaryKeyName(), 'Input', ['type' => 'hidden']);
                                }
                                if (\Admin::isInsert()) {
                                    echo $form->addInput('id_usuario', 'Input', ['type' => 'hidden', 'value' => Login::current()->primaryKey()]);
                                }else if(\Admin::isUpdate()){
                                    echo $form->addInput('id_usuario', 'Input', ['type' => 'hidden']);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">Produto</h5>
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php
                                                        echo $form->addInput('nome', 'Input',
                                                            [],
                                                            [
                                                                'class' => ['col-md-6']
                                                            ]
                                                        );
                                                        echo $form->addInput('cod_barras', 'Input',
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
                                                        <?php $options = array2htmlselect((new Estados())
                                                            ->select()
                                                            ->from(['CATEGORIA' => 'produtos_categorias'])
                                                            ->orderBy(ELOQUENT_ASC, 'CATEGORIA.nome')
                                                            ->execute(true), 'nome', 'id_categoria');
                                                        $options[''] = 'Selecione uma categoria';
                                                        ksort($options);
                                                        echo $form->addInput('id_categoria', 'Select',
                                                            [
                                                                'options' => $options,
                                                            ],
                                                            [
                                                                'class' => ['col-md-6'],
                                                            ]
                                                        ); ?>
                                                        <?php
                                                        $options = ['' => 'Selecione uma categoria primeiro'];
                                                        if (Session::get('action') == 'update') {
                                                            $options = array2htmlselect((new ProdutosSubcategorias())
                                                                ->select()
                                                                ->from(['PS' => ProdutosSubcategorias::tableName()])
                                                                ->where(ELOQUENT_EQUAL, ['PS.id_categoria' => $model->id_categoria])
                                                                ->orderBy(ELOQUENT_ASC, 'PS.nome')
                                                                ->execute(true), 'nome', 'id_subcategoria');
                                                            $options[''] = 'Selecione uma subcategoria';
                                                        }
                                                        ksort($options);
                                                        echo $form->addInput('id_subcategoria', 'Select',
                                                            [
                                                                'options' => $options,
                                                            ],
                                                            [
                                                                'class' => ['col-md-6'],
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php

                                                        echo $form->addInput('valor_unitario', 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'data-disabled' => !Login::currentIsAdm() && \Admin::isUpdate() ? 'true' : 'false'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        echo $form->addInput('valor_atacado', 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'data-disabled' => !Login::currentIsAdm() && \Admin::isUpdate() ? 'true' : 'false'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        echo $form->addInput('valor_minimo', 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'data-disabled' => !Login::currentIsAdm() && \Admin::isUpdate() ? 'true' : 'false'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );

                                                        echo $form->addInput('valor_de_custo', 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'data-disabled' => !Login::currentIsAdm() && \Admin::isUpdate() ? 'true' : 'false'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-3']
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('quantidade_estoque', 'Input',
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
                                                        $form->addInput('descricao', 'Textarea',
                                                            [
                                                                'attrs' => [
                                                                    'style' => 'min-height: 150px'
                                                                ],
                                                            ],
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
                                                        $form->addInput('status', 'Boolui',
                                                            [
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
