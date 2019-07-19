<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Form\Form;
use Winged\Model\News;
use Winged\Model\Login;
use Winged\Model\NewsCategorias;
use Winged\Database\DbDict;

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
                                <?= $form->begin((Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true); ?>
                                <?php
                                if ($model->primaryKey()) {
                                    echo $form->addInput(News::primaryKeyName(), 'Input', ['type' => 'hidden']);
                                }
                                if (Admin::isInsert()) {
                                    echo $form->addInput('id_usuario', 'Input', ['type' => 'hidden', 'value' => Login::current()->primaryKey()]);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">News</h5>
                                        <div class="panel-body">

                                            <div class="col-md-12">
                                                <!--div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                $form->addInput('post_type', 'Boolui',
                                                    [
                                                        'value' => 1,
                                                        'attrs' => [
                                                            'checked' => $model->post_type == 1 ? 'checked' : '',
                                                        ],
                                                    ],
                                                    [
                                                        'class' => ['col-md-12']
                                                    ]
                                                ); ?>
                                                    </div>
                                                    <span class="help-block">Definir o post como "Somente imagem" desabilita todos os textos para leitura inclusive o titulo no site, campos continuam obrigatórios para indexação do Google e compartilhamentos em redes sociais. </span>
                                                </div-->
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php
                                                        echo $form->addInput('titulo', 'Input',
                                                            [],
                                                            [
                                                                'class' => ['col-md-6']
                                                            ]
                                                        );
                                                        echo $form->addInput('og_title', 'Input',
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
                                                        <?php

                                                        $values = array2htmlselect((new NewsCategorias())->select(['C.*'])
                                                            ->from(['C' => NewsCategorias::tableName()])
                                                            ->orderBy(ELOQUENT_ASC, 'C.categoria')
                                                            ->execute(true), 'categoria', 'id_categoria');

                                                        echo $form->addInput('categorias', 'Checkbox',
                                                            [
                                                                'values' => $values,
                                                            ],
                                                            [
                                                                'class' => ['col-md-12'],
                                                                'selectors' => [
                                                                    '.checkbox' => [
                                                                        'class' => [
                                                                            'inline',
                                                                            'row',
                                                                        ]
                                                                    ],
                                                                    '.checkbox > div' => [
                                                                        'class' => [
                                                                            'col-lg-3',
                                                                            'col-md-3',
                                                                            'col-sm-6',
                                                                            'col-xs-12',
                                                                        ]
                                                                    ]
                                                                ]
                                                            ],
                                                            true
                                                        );
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('previa', 'Textarea',
                                                            [
                                                                'attrs' => [
                                                                    'style' => 'min-height: 150px',
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
                                                        $form->addInput('og_description', 'Textarea',
                                                            [
                                                                'attrs' => [
                                                                    'style' => 'min-height: 150px',
                                                                ]
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
                                                        $form->addInput('post', 'Textarea',
                                                            [
                                                                'class' => ['summernote']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>

                                                <!--div class="form-group">
                                                    <div class="row">
                                                        <?= $form->addInput('tipo', 'Radio',
                                                    [
                                                        'values' => [
                                                            'Imagem',
                                                            'Video'
                                                        ]
                                                    ],
                                                    [
                                                        'class' => ['col-md-6']
                                                    ]
                                                ); ?>
                                                    </div>
                                                </div-->

                                                <!--div class="fadeVideo form-group">
                                                    <div class="row">
                                                        <?=
                                                $form->addInput('youtube', 'Input',
                                                    [],
                                                    [
                                                        'class' => ['col-md-12']
                                                    ]
                                                );
                                                ?>
                                                    </div>
                                                </div-->

                                                <div class="form-group">
                                                    <div class="row">
                                                        <input type="hidden" name="Winged\Model\News[imagem]"
                                                               value="<?php if ($model->getImagem() != false) {
                                                                   echo 'keep';
                                                               } elseif ($model->hasErrors()) {
                                                                   echo $model->imagem;
                                                               } ?>">
                                                        <div class="col-lg-12">
                                                            <div tabindex="500" class="btn btn-primary btn-file">
                                                                <i class="icon-file-plus"></i>
                                                                <span class="hidden-xs">Selecione uma imagem</span>
                                                                <input name="upload" type="file" class="file-input">
                                                                <input type="hidden" name="folder"
                                                                       value="./uploads/buffer/">
                                                                <input type="hidden" name="width" value="700">
                                                                <input type="hidden" name="height" value="485">
                                                            </div>
                                                            <button type="button" class="crop btn btn-success">
                                                                Recortar
                                                            </button>
                                                            <button type="button" class="remove-img btn btn-danger">
                                                                Remover imagem
                                                            </button>
                                                            <span class="help-block">Imagem principal da notícia (Observação: a imagem só será removida no momento em que o resgistro for salvo)</span>
                                                            <span class="help-block">Tamanho da imagem: <b>700 x 485</b></span>
                                                            <?php
                                                            if (array_key_exists('imagem', $model->getErrors())) {
                                                                ?>
                                                                <span class="help-block text-danger-400">
                                                                    <b><?= $model->getErrors()['imagem'][0] ?></b>
                                                                </span>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <?php
                                                            if ($model->getImagem() != false) {
                                                                ?>
                                                                <img src="<?= $model->getImagem() ?>"/>
                                                                <?php
                                                            } else if ($model->hasErrors()) {
                                                                $image = new \Winged\Image\Image($model->imagem, false);
                                                                if ($image->exists()) {
                                                                    ?>
                                                                    <img src="../<?= $image->file_path ?>"/>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <input type="hidden" name="Winged\Model\News[og_imagem]"
                                                               value="<?php if ($model->getImagem('og_imagem') != false) {
                                                                   echo 'keep';
                                                               } elseif ($model->hasErrors()) {
                                                                   echo $model->og_imagem;
                                                               } ?>">
                                                        <div class="col-lg-12">
                                                            <div tabindex="500" class="btn btn-primary btn-file">
                                                                <i class="icon-file-plus"></i>
                                                                <span class="hidden-xs">Selecione uma imagem</span>
                                                                <input name="upload" type="file" class="file-input">
                                                                <input type="hidden" name="folder"
                                                                       value="./uploads/buffer/">
                                                                <input type="hidden" name="width" value="1200">
                                                                <input type="hidden" name="height" value="628">
                                                            </div>
                                                            <button type="button" class="crop btn btn-success">
                                                                Recortar
                                                            </button>
                                                            <button type="button" class="remove-img btn btn-danger">
                                                                Remover imagem
                                                            </button>
                                                            <span class="help-block">Og:image (Observação: a og_imagem só será salva no momento em que o resgistro for salvo)</span>
                                                            <span class="help-block">Tamanho da imagem: <b>1200 x 628</b></span>
                                                            <?php
                                                            if (array_key_exists('og_imagem', $model->getErrors())) {
                                                                ?>
                                                                <span class="help-block text-danger-400">
                                                                    <b><?= $model->getErrors()['og_imagem'][0] ?></b>
                                                                </span>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <?php
                                                            if ($model->getImagem('og_imagem') != false) {
                                                                ?>
                                                                <img src="<?= $model->getImagem('og_imagem') ?>"/>
                                                                <?php
                                                            } else if ($model->hasErrors()) {
                                                                if (file_exists('../' . $model->og_imagem) && is_file('../' . $model->og_imagem)) {
                                                                    ?>
                                                                    <img src="../<?= $model->og_imagem ?>"/>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label for="file-styled">Anexos: </label>
                                                    <?php

                                                    /**
                                                     * @var $this Controller
                                                     */

                                                    if (!empty($model->anexos)) {
                                                        ?>
                                                        <div class="files">
                                                            <p class="help-block text-success-400">
                                                                <i class="glyphicon glyphicon-info-sign"></i>
                                                                Arquivos já anexados.
                                                            </p>
                                                            <div style="margin-bottom: 10px;">
                                                                <?php
                                                                if (is_array($model->anexos)) {
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
                                                        if (is_array($model->anexos)) {
                                                            foreach ($model->anexos as $key => $anexo) {
                                                                ?>
                                                                <input data-id="<?= $anexo ?>" type="hidden"
                                                                       name="Winged\Model\News[keep_files][]"
                                                                       class="keep_files"
                                                                       value="<?= $anexo ?>"/>
                                                                <?php
                                                            }
                                                        }
                                                    } else {
                                                        ?>
                                                        <input type="hidden" name="Winged\Model\News[keep_files]"
                                                               value=""/>
                                                        <?php
                                                    }

                                                    ?>
                                                    <div class="uploader" id="uniform-file-styled">
                                                        <input type="file" id="file-styled"
                                                               class="alpaca-control" name="Winged\Model\News[anexos][]"
                                                               autocomplete="off" multiple/>
                                                    </div>

                                                    <p class="help-block ">
                                                        <i class="glyphicon glyphicon-info-sign"></i>
                                                        Escolha fotos, documentos .doc ou arquivos .pdf
                                                    </p>
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

