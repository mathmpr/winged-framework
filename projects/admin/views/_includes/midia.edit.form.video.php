<?php

/**
 * @var $midia Midias
 */

use Winged\Form\Form;

$form = new Form($midia);

echo $form->begin('#', 'post', ['id' => 'update-midia-form'], 'multipart/form-data', true);

echo $form->addInput(Midias::primaryKeyName(), 'Input', ['type' => 'hidden'], ['class' => ['no-display']]);

?>

    <div class="row mt-25">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

            <div class="form-group">
                <div class="row">
                    <?=
                    $form->addInput('alt_attr', 'Input',
                        [],
                        [
                            'class' => ['col-md-12'],
                        ]
                    );
                    ?>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <?=
                    $form->addInput('legenda', 'Textarea',
                        [],
                        [
                            'class' => ['col-md-12'],
                        ]
                    );
                    ?>
                </div>
            </div>

            <div class="form-group mb-5">
                <div class="row">
                    <?=
                    $form->addInput('centralizar_legenda', 'Boolui',
                        [
                            'value' => 1,
                            'attrs' => [
                                'checked' => $midia->centralizar_legenda ? 'checked' : '',
                            ],
                        ],
                        [
                            'class' => ['col-md-12']
                        ]
                    ); ?>
                </div>
            </div>

            <div class="others">

                <div class="toggle no-display">
                    <div class="form-group">
                        <div class="row">
                            <?=
                            $form->addInput('father_id', 'Input',
                                [],
                                [
                                    'class' => ['col-md-12'],
                                ]
                            );
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <?=
                            $form->addInput('element_id', 'Input',
                                [],
                                [
                                    'class' => ['col-md-12'],
                                ]
                            );
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <?=
                            $form->addInput('father_classes', 'Textarea',
                                [],
                                [
                                    'class' => ['col-md-12'],
                                ]
                            );
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <?=
                            $form->addInput('element_classes', 'Textarea',
                                [],
                                [
                                    'class' => ['col-md-12'],
                                ]
                            );
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <?=
                            $form->addInput('style_attr', 'Textarea',
                                [],
                                [
                                    'class' => ['col-md-12'],
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <span id="other-config" class="help-block text-primary cursor-pointer display-inline-block">Outras configurações</span>
                        <button class="btn btn-primary display-inline-block make-update" style="float: right;"><i class="icon-checkmark3"></i> Salvar</button>
                        <button class="btn btn-primary display-inline-block replace mr-10" style="float: right;"><i class="icon-quill4"></i> Substituir arquivo</button>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        var elems = document.querySelectorAll('#update-midia-form .switchery');
        for (var i = 0; i < elems.length; i++) {
            var switchery = new Switchery(elems[i]);
        }

        $("#other-config").on('click', function () {
            $('.toggle.no-display').slideToggle();
        });
    </script>

<?php

$form->end();