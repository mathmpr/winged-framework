<?php
$this->html('_includes/navbar');
$this->html('_includes/content');
$this->html('_includes/menu');

use Winged\Database\DbDict;
use Winged\Form\Form;
use Winged\Model\Cidades;
use Winged\Model\Estados;

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
                                    echo $form->addInput(Cidades::primaryKeyName(), 'Input', ['type' => 'hidden'], [], false, true);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">Cidade</h5>
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('cidade', 'Input',
                                                            [],
                                                            [
                                                                'class' => ['col-md-6']
                                                            ], false, true
                                                        );
                                                        ?>
                                                        <?php $options = array2htmlselect((new Estados())
                                                            ->select()
                                                            ->from(['ESTADOS' => 'estados'])
                                                            ->orderBy(ELOQUENT_ASC, 'ESTADOS.estado')
                                                            ->execute(true), 'estado', 'id_estado');
                                                        $options[''] = 'Selecione um estado';
                                                        ksort($options);
                                                        echo $form->addInput('id_estado', 'Select',
                                                            [
                                                                'options' => $options,
                                                            ],
                                                            [
                                                                'class' => ['col-md-6'],
                                                            ], false, true
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
                                                        ], [], false, true
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
