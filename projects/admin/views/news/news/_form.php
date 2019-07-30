<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Form\Form;
use Winged\Formater\Formater;

/**
 * @var $model News
 */

$newsSlug = Slugs::getSlug($model->primaryKey(), News::tableName())->slug;
$editSlug = Formater::toUrl($model->title_tag) === $newsSlug;

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

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('titulo', 'Input',
                                                            [],
                                                            [
                                                                'class' => ['col-md-4'],
                                                                'selectors' => [
                                                                    'label:first-child' => [
                                                                        'after' => '<span class="help-block">Tente não utilizar um título muito grande, pois isso desvaloriza seu conteúdo perante aos mecanismos de buscas. Tente se limitar a 100 caracteres.</span>'
                                                                    ]
                                                                ]
                                                            ]
                                                        );
                                                        ?>
                                                        <?=
                                                        $form->addInput('title_tag', 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'data-slug' => '#slug'
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-4'],
                                                                'selectors' => [
                                                                    'label:first-child' => [
                                                                        'after' => '<span class="help-block">Esse é o titulo que os buscadores irão utilizar para indexar seu conteúdo. Também é o nome em que aparece na aba do navegador. Você não pode escrever mais que 60 caracteres neste campo.</span>'
                                                                    ]
                                                                ]
                                                            ]
                                                        );
                                                        ?>
                                                        <?=
                                                        $form->addInput(null, 'Input',
                                                            [
                                                                'attrs' => [
                                                                    'id' => 'slug',
                                                                    'name' => 'slug',
                                                                    'data-linkTo' => $model->primaryKey(),
                                                                    'data-tableName' => News::tableName()
                                                                ],
                                                                'class' => [
                                                                    $editSlug ? '' : 'cant-edit', 'core-slugify'
                                                                ],
                                                                'value' => $newsSlug == 'n-a' ? '' : $newsSlug,
                                                                'selectors' => [
                                                                    'label' => [
                                                                        'text' => 'URL amigável do post (Slug):'
                                                                    ]
                                                                ]
                                                            ],
                                                            [
                                                                'class' => ['col-md-4'],
                                                                'selectors' => [
                                                                    'label:first-child' => [
                                                                        'after' => '<span class="help-block">Caso não seja preenchido, o campo será autopreenchido. Possúi as mesma regras do campo anterior. Porém este é responsavel por uma leitura mais amigável na URL.</span>'
                                                                    ]
                                                                ]
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?php

                                                        $values = array2htmlselect(array_merge([0 => ['id_categoria' => 0, 'categoria' => 'Selecione uma categoria principal']], (new NewsCategorias())->select(['C.*'])
                                                            ->from(['C' => NewsCategorias::tableName()])
                                                            ->orderBy(ELOQUENT_ASC, 'C.categoria')
                                                            ->execute(true)), 'categoria', 'id_categoria');

                                                        echo $form->addInput('id_categoria', 'Select',
                                                            [
                                                                'options' => $values,
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        );
                                                        ?>

                                                    </div>
                                                </div>

                                                <fieldset id="cursosEventos" class="scheduler-border no-display">
                                                    <legend class="scheduler-border">Local e hora do evento / curso
                                                    </legend>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <?php

                                                            echo $form->addInput('hora_inicio', 'Input', [
                                                                'attrs' => [
                                                                    'class' => 'datepicker form-control'
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => [
                                                                        'col-md-6',
                                                                    ]
                                                                ]
                                                            );

                                                            echo $form->addInput('hora_fim', 'Input', [
                                                                'attrs' => [
                                                                    'class' => 'datepicker form-control'
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => [
                                                                        'col-md-6',
                                                                    ]
                                                                ]
                                                            );

                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <?php

                                                            echo $form->addInput('cep', 'Input', [
                                                                'attrs' => [
                                                                    'class' => Admin::isInsert() ? 'form-control fetch-all' : 'form-control fetch-null'
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => [
                                                                        'col-md-3',
                                                                    ]
                                                                ]
                                                            );
                                                            echo $form->addInput('estado', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->estado == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-3']
                                                                ]
                                                            );

                                                            echo $form->addInput('cidade', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->cidade == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-3']
                                                                ]
                                                            );

                                                            echo $form->addInput('bairro', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->bairro == '' ? true : false
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

                                                            <?php

                                                            echo $form->addInput('uf', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->uf == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-2']
                                                                ]
                                                            );

                                                            echo $form->addInput('rua', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->rua == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-4']
                                                                ]
                                                            );

                                                            echo $form->addInput('complemento', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->complemento == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-4']
                                                                ]
                                                            );

                                                            echo $form->addInput('numero', 'Input', [
                                                                'attrs' => [
                                                                    'readonly' => Admin::isInsert() || $model->numero == '' ? true : false
                                                                ]
                                                            ],
                                                                [
                                                                    'class' => ['col-md-2']
                                                                ]
                                                            );


                                                            ?>
                                                        </div>
                                                    </div>
                                                </fieldset>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label for="News_categorias">Outras categorias deste
                                                                post: </label>
                                                            <input name="News[categorias]" id="News_categorias"
                                                                   type="text"
                                                                   class="form-control">
                                                            <?php
                                                            $categorias = [];
                                                            if (!empty($model->categorias)) {
                                                                foreach ($model->categorias as $categoria) {
                                                                    $categorias[] = [
                                                                        "id_categoria" => $categoria->id_categoria,
                                                                        "categoria" => $categoria->extras('categoria')
                                                                    ];
                                                                }
                                                            }
                                                            ?>
                                                            <script>var newsCategorias = <?= json_encode($categorias) ?>;</script>
                                                        </div>
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
                                                        $form->addInput('post', 'Textarea',
                                                            [
                                                                'class' => ['summernote', 'perm-midias']
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>

                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border">Galeria de fotos e vídeos
                                                    </legend>
                                                    <div class="form-group">
                                                        <button class="galeria-news btn bg-primary-400 mb-20">Adicionar
                                                            mídias na galeria
                                                        </button>
                                                        <div id="newsGaleria" class="row">
                                                            <?php
                                                            if (!empty($model->galeria)) {
                                                                foreach ($model->galeria as $midia) {
                                                                    if(is_object($midia)){
                                                                        if ($midia->isImage()) {
                                                                            ?>
                                                                            <div class="mt-20 col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                                                                <div class="min-height-news"><span
                                                                                            class="icon-cross2"></span><input
                                                                                            type="hidden"
                                                                                            name="News[galeria][]"
                                                                                            value="<?= $midia->primaryKey() ?>">
                                                                                    <div style="background-image: url(<?= $midia->getFileUrl() ?>)"></div>
                                                                                </div>
                                                                            </div>
                                                                            <?php
                                                                        } else {
                                                                            ?>
                                                                            <div class="mt-20 col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                                                                <div class="min-height-news"><span
                                                                                            class="icon-cross2"></span><input
                                                                                            type="hidden"
                                                                                            name="News[galeria][]"
                                                                                            value="<?= $midia->primaryKey() ?>">
                                                                                    <div>
                                                                                        <video muted loop autoplay>
                                                                                            <source src="<?= $midia->getFileUrl() ?>"
                                                                                                    type="<?= $midia->getFile()->getMimeType() ?>">
                                                                                        </video>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </fieldset>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('image', 'Input',
                                                            [
                                                                'type' => 'hidden',
                                                                'class' => ['midia-info'],
                                                                'value' => is_object($model->image) ? $model->image->id : $model->image
                                                            ],
                                                            [
                                                                'class' => ['col-md-4'],
                                                                'selectors' => [
                                                                    'label:first-child' => [
                                                                        'after' => '<button class="is-midia open-midia-image-one btn bg-primary-400 mb-20">Imagem principal para o post</button>',
                                                                        'class' => ['no-display'],
                                                                    ],
                                                                ]
                                                            ]
                                                        );
                                                        ?>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('is_video', 'Boolui',
                                                            [
                                                                'value' => 1,
                                                                'attrs' => [
                                                                    'checked' => $model->is_video == 1 ? 'checked' : '',
                                                                ],
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group is-video no-display">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('from_youtube', 'Boolui',
                                                            [
                                                                'value' => 1,
                                                                'attrs' => [
                                                                    'checked' => $model->from_youtube == 1 ? 'checked' : '',
                                                                ],
                                                            ],
                                                            [
                                                                'class' => ['col-md-12']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <div class="form-group is-video no-display">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('video_source', 'Input',
                                                            [
                                                                'type' => 'hidden',
                                                                'class' => ['midia-info'],
                                                                'value' => is_object($model->video_source) ? $model->video_source->id : $model->video_source
                                                            ],
                                                            [
                                                                'class' => ['col-md-4'],
                                                                'selectors' => [
                                                                    'label:first-child' => [
                                                                        'after' => '<button class="is-midia open-midia-video-one btn bg-primary-400 mb-20">Vídeo para a capa do post</button>',
                                                                        'class' => ['no-display'],
                                                                    ],
                                                                ]
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
                                                            'text' => Admin::isInsert() ? 'Inserir' : 'Salvar',
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
            <?= $this->html('_includes/midias.modal'); ?>
        </div>
    </div>
<?php
$this->html('_includes/end.content');