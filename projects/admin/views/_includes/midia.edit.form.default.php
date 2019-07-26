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
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <span id="other-config" class="help-block text-primary cursor-pointer display-inline-block">Somente substituição</span>
                        <button class="btn btn-primary display-inline-block replace mr-10" style="float: right;"><i
                                    class="icon-quill4"></i> Substituir arquivo
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php

$form->end();