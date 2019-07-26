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
                                    echo $form->addInput(SeoPages::primaryKeyName(), 'Input', ['type' => 'hidden']);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title"><?= $model->page_title == '' ? 'Inserindo nova página' : 'Editando SEO da página: ' . $model->page_title ?></h5>
                                        <div class="panel-body">

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('page_title', 'Input',
                                                            [
                                                                'attrs' => ['max-length' => '150'],
                                                                'class' => ['seo-length']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12'],
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="find-prev col-md-6">
                                                            <div class="row">
                                                                <?= $form->addInput('slug', 'Input',
                                                                    [
                                                                        'attrs' => ['max-length' => '255'],
                                                                        'class' => ['seo-length']
                                                                    ],
                                                                    [
                                                                        'class' => ['col-md-12']
                                                                    ]
                                                                ); ?>
                                                            </div>
                                                            <span class="help-block">Todas as configurações desta página serão mostradas caso o nome do controller combine com o valor inserido neste campo. <b>Do contrario as configurações aqui não serão exibidas. Falhas nesse campo deixarão o campo vermelho em tempo real enquanto o mesmo é preenchido, tal situação não afetará salvar o formulário.</b></span>
                                                            <span class="help-block">Não crie URLS longas demais, isso pode prejudicar a relevancia de seu conteúdo. <b>Use no máximo cinco palavras.</b></span>
                                                            <span class="help-block"><b>Caso não seja informada, a URL da página sera uma cópia formatada do <span
                                                                            class="text-success-400">Título H1.</b></span></span>
                                                            <span class="error-msg help-block text-danger-400"></span>
                                                        </div>
                                                        <div class="find-prev col-md-6">
                                                            <div class="row">
                                                                <?= $form->addInput('keywords', 'Input',
                                                                    [
                                                                        'attrs' => ['max-length' => '255'],
                                                                        'class' => ['seo-length']
                                                                    ],
                                                                    [
                                                                        'class' => ['col-md-12']
                                                                    ]
                                                                ); ?>
                                                            </div>
                                                            <span class="help-block"><b>É fortemente recomendado que você não use mais de uma palavra chave por página. É permitido mais de uma palavra sem utilizar virgula. A utilização da virgula caracteriza o texto como mais de uma palavra chave.</b></span>
                                                            <span class="help-block"><b>É melhor que a palavra chave exista no <span
                                                                            class="text-success-400">Título H1</span> da página.</b></span>
                                                            <span class="error-msg help-block text-danger-400"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('description', 'Textarea',
                                                            [
                                                                'attrs' => [
                                                                    'style' => 'min-height: 50px; resize: none;',
                                                                    'max-length' => '153'
                                                                ],
                                                                'class' => ['seo-length']
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
                                                        <?= $form->addInput('canonical_url', 'Input',
                                                            [
                                                                'attrs' => ['max-length' => '255'],
                                                                'class' => ['seo-length']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                    <span class="help-block"><b>Informa qual a URL da página, sendo considerada a frente da URL real. Permite driblar a acusação de contúdos duplicados.</b> Informar uma URL aqui faz com que está página seja considerada como a da URL informada.</span>
                                                    <span class="help-block"><b>Caso nenhuma URL seja informada, a URL canonica será informada dinâmicamente através de uma cópia da <span
                                                                    class="text-success-400">URI sem GET.</span></b></span>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?= $form->addInput('fb_title', 'Input',
                                                            [
                                                                'attrs' => ['max-length' => '255'],
                                                                'class' => ['seo-length']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                    <span class="help-block"><b>Sinônimo de Og:title</b></span>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('fb_description', 'Textarea',
                                                            [
                                                                'attrs' => [
                                                                    'style' => 'min-height: 50px; resize: none;',
                                                                    'max-length' => '500'
                                                                ],
                                                                'class' => ['seo-length']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                    <span class="help-block"><b>Sinônimo de Og:description</b></span>
                                                </div>
                                                <div class="fadeImage form-group">
                                                    <div class="row">
                                                        <input type="hidden" name="Winged\Model\SeoPages[fb_image]"
                                                               value="<?php if ($model->getImagem() != false) {
                                                                   echo 'keep';
                                                               } elseif ($model->hasErrors()) {
                                                                   echo $model->fb_image;
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
                                                            <span class="help-block">Og:image (Obs: a imagem só será salva no momento em que o resgistro for salvo)</span>
                                                            <?php
                                                            if (array_key_exists('fb_image', $model->getErrors())) {
                                                                ?>
                                                                <span class="help-block text-danger-400">
                                                                    <b><?= $model->getErrors()['fb_image'][0] ?></b>
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
                                                                if (file_exists('../' . $model->fb_image) && is_file('../' . $model->fb_image)) {
                                                                    ?>
                                                                    <img src="../<?= $model->fb_image ?>"/>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </div>
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

