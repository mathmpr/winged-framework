<?php

use Winged\Winged;

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
            <div class="panel panel-white">
                <div class="panel-heading">
                    <h6 class="panel-title">Detalhes da obra</h6>
                    <a class="heading-elements-toggle"><i class="icon-more"></i></a></div>

                <div class="panel-body no-padding-bottom">
                    <div class="row">
                        <div class="col-sm-6 content-group">
                            <ul class="list-condensed list-unstyled">
                                <li><span class="text-semibold">Nome: </span><?= $model->nome ?></li>
                                <li>
                                    <span class="text-semibold">Endereço: </span><?= $model->estadoNome . ' - ' . $model->cidadeNome . ' - ' . $model->bairroNome . ' - ' . $model->rua . ', ' . $model->numero ?>
                                </li>
                                <li><span class="text-semibold">CEP: </span><?= $model->cep ?></li>
                                <li>
                                    <span class="text-semibold">Complemento: </span><?= $model->complemento == '' ? 'N/A' : $model->complemento ?>
                                </li>
                                <?php

                                if (count7($model->anexos) > 0) {
                                    ?>
                                    <li>
                                        <button onclick="window.open('<?= Winged::$protocol . $model->anexos[0] ?>', '_blank')" type="button" class="btn btn-primary btn-labeled btn-xs">
                                            <b>
                                                <i class="icon-paperplane"></i>
                                            </b>
                                            Visualizar contrato
                                        </button>
                                    </li>
                                    <?php
                                }

                                ?>

                            </ul>
                        </div>

                        <div class="col-sm-6 content-group">
                            <div class="invoice-details">
                                <h5 class="text-uppercase text-semibold">Obra
                                    #<?= str_pad($model->primaryKey(), 5, '0', STR_PAD_LEFT); ?></h5>
                                <ul class="list-condensed list-unstyled">
                                    <li>Data de cadastro: <span class="text-semibold"><?= $model->data_cadastro->custom('%d %b %Y', true, ['de']); ?></span></li>
                                    <li>Data de modificação: <span class="text-semibold"><?= $model->data_alteracao->custom('%d %b %Y', true, ['de']); ?></span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-12 content-group">
                            <span class="text-muted">Endereço da obra detalhado: </span>
                            <ul class="list-condensed list-unstyled">
                                <li><span class="text-semibold">Estado: </span><?= $model->estadoNome ?></li>
                                <li><span class="text-semibold">Cidade: </span><?= $model->cidadeNome ?></li>
                                <li><span class="text-semibold">Bairro: </span><?= $model->bairroNome ?></li>
                                <li><span class="text-semibold">Cep: </span><?= $model->cep ?></li>
                                <li><span class="text-semibold">Rua: </span><?= $model->rua ?></li>
                                <li><span class="text-semibold">Número: </span><?= $model->numero ?></li>
                            </ul>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 col-lg-9 content-group">
                            <span class="text-muted">Seus dados:</span>
                            <ul class="list-condensed list-unstyled">
                                <li><span class="text-semibold">Nome: </span> <?= $usuario->nome ?></li>
                                <li><span class="text-semibold">E-mail: </span> <?= $usuario->email ?></li>
                                <?php

                                if($usuario->cnpj !== '' && $usuario->cpf === ''){
                                    ?>
                                    <li><span class="text-semibold">CNPJ: </span> <?= $usuario->cnpj ?></li>
                                    <li><span class="text-semibold">Nome fantasia: </span> <?= $usuario->nome_fantasia != '' ? $usuario->nome_fantasia : 'Não informada' ?></li>
                                    <li><span class="text-semibold">Razao social: </span> <?= $usuario->razao_social != '' ? $usuario->razao_social : 'Não informada' ?></li>
                                    <?php
                                }else if($usuario->cnpj === '' && $usuario->cpf !== ''){
                                    ?>
                                    <li><span class="text-semibold">CPF: </span> <?= $usuario->cpf ?></li>
                                    <?php
                                }else if($usuario->cnpj !== '' && $usuario->cpf !== ''){
                                    ?>
                                    <li><span class="text-semibold">CPF: </span> <?= $usuario->cpf ?></li>
                                    <li><span class="text-semibold">CNPJ: </span> <?= $usuario->cnpj ?></li>
                                    <li><span class="text-semibold">Nome fantasia: </span> <?= $usuario->nome_fantasia != '' ? $usuario->nome_fantasia : 'Não informada' ?></li>
                                    <li><span class="text-semibold">Razao social: </span> <?= $usuario->razao_social != '' ? $usuario->razao_social : 'Não informada' ?></li>
                                    <?php
                                }

                                ?>
                                <li><span class="text-semibold">Telefone: </span> <?= $usuario->telefone ?></li>
                                <li><span class="text-semibold">Celular: </span> <?= $usuario->celular ?></li>

                            </ul>
                        </div>

                        <div class="col-md-6 col-lg-3 content-group">
                            <span class="text-muted">Detalhes do seu endereço:</span>
                            <ul class="list-condensed list-unstyled invoice-payment-details">
                                <li><?= $usuario->estadoNome ?><span class="text-semibold">Estado</span></li>
                                <li><?= $usuario->cidadeNome ?><span class="text-semibold">Cidade</span></li>
                                <li><?= $usuario->bairroNome ?><span class="text-semibold">Bairro</span></li>
                                <li><?= $usuario->cep ?><span class="text-semibold">Cep</span></li>
                                <li><?= $usuario->rua ?><span class="text-semibold">Rua</span></li>
                                <li><?= $usuario->numero ?><span class="text-semibold">Número</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->html('_includes/end.content');
