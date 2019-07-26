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

            $form = new Form($model);

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

            if ($success) {
                ?>
                <div style="margin-top: 10px" class="col-lg-12">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-check"></i> Config de SEO salva com sucesso</h6>
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

            ?>

            <div class="col-lg-12">
                <?= $form->begin('default', 'post', [], 'multipart/form-data', true); ?>
                <?php
                if (Admin::isUpdate()) {
                    echo $form->addInput('id_seo', 'Input', ['type' => 'hidden']);
                }
                ?>
                <div class="panel panel-flat">
                    <div class="panel-heading">
                        <h5 class="panel-title">SEO Config</h5>
                        <div class="panel-body">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('ga_status', 'Boolui',
                                            [
                                                'value' => 1,
                                                'attrs' => [
                                                    'checked' => $model->ga_status == 1 ? 'checked' : '',
                                                ],
                                            ],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        ); ?>
                                    </div>
                                    <span class="help-block">Habilita a função principal do Google Analitycs "ga()" no javascript de todas as páginas. Desabilitar <b>não irá</b> causar erros no javascript ao usar a função "ga()". <b>Se o site estiver em desenvolvimento é recomendado que está opção permaneça desativada.</b></span>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('append_seo', 'Boolui',
                                            [
                                                'value' => 1,
                                                'attrs' => [
                                                    'checked' => $model->append_seo == 1 ? 'checked' : '',
                                                ],
                                            ],
                                            [
                                                'class' => ['col-md-12']
                                            ]
                                        ); ?>
                                    </div>
                                    <span class="help-block">Os problemas aparecerão no console do navegador.</span>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <?=
                                        $form->addInput('uid', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('site_name', 'Input',
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
                                        $form->addInput('author', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('publisher', 'Input',
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
                                        $form->addInput('language', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('robots', 'Input',
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
                                        $form->addInput('revisit_after', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-6']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('location', 'Input',
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
                                        $form->addInput('geo_position', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-4']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('geo_placename', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-4']
                                            ]
                                        );
                                        ?>
                                        <?=
                                        $form->addInput('geo_region', 'Input',
                                            [],
                                            [
                                                'class' => ['col-md-4']
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

<?php

if (Login::current()->change_pass_please == 1) {
    ?>
    <script>
        window.addEventListener("load", function () {
            $('#trigger').trigger('click');
        }, false);
    </script>
    <?php
}

?>

    <button id="trigger" style="display: none;" type="button" class="btn btn-default btn-sm" data-toggle="modal"
            data-target="#modal_default"></button>
    <div id="modal_default" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h5 class="modal-title">Altere sua senha por favor</h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning alert-styled-left text-slate-800 content-group">
                        <span class="text-semibold">Alterar senha</span> confirmação da ação
                        <button type="button" class="close" data-dismiss="alert">Ã—</button>
                    </div>
                    <p>Sua senha ainda é a senha padrão estabelicida pelo desenvolvedor desde sistema. Por gentileza
                        clique no botão "Alterar agora", preencha o campo de senha no formulário, o campo de repetição
                        de
                        senha e faça o envio dos dados clicando no botão enviar.</p>
                    <p>Feito isso este aviso irá parar de aparecer. Atenciosamente.</p>
                    <hr>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" type="button" class="btn btn-link">Deixar para mais tarde</button>
                    <button onclick="window.location = '<?= \Winged\Winged::$protocol ?>admin/usuarios/update/<?= Login::current()->primaryKey() ?>'; return false;"
                            class="btn bg-primary-400"><i
                                class="icon-check"></i> Alterar agora
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php
$this->html('_includes/end.content');