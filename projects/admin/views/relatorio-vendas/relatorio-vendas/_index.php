<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Form\Form;

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
                            <h6 class="panel-title"><i class="icon-check"></i> Impossível gerar o relatório</h6>
                        </div>
                        <div class="panel-body">
                            <h6 class="no-margin">
                                <small class="display-block" style="margin-bottom:10px">
                                    Sua última ação executada porquê o sistema
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

            <script>var clientes = <?= json_encode($model->clientes) ?></script>
            <script>var bairros = <?= json_encode($model->bairros) ?></script>
            <script>var produtos = <?= json_encode($model->produtos) ?></script>
            <script>var _status = <?= json_encode($model->status) ?></script>

            <?php

            $model->clientes = '';
            $model->bairros = '';
            $model->produtos = '';
            $model->status = '';

            ?>

            <div class="page-container">
                <div class="page-content">
                    <div class="content-wrapper">
                        <div class="content">
                            <div class="row">
                                <?= $form->begin((\Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true); ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">Relatório de vendas</h5>
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
