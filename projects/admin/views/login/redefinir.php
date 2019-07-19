<?php

use Winged\Form\Form;
use Winged\Winged;
use Winged\Model\Login;

$login = (new Login());
$form = new Form($login);

?>
<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="content">
                <div class="panel panel-body login-form">
                    <div class="text-center">
                        <div class="border-slate-100 text-slate-100">
                            <div class="circle">
                                <?= $this->seo ?>
                            </div>
                        </div>
                        <?= $message ?>
                    </div>
                    <?= $form->begin('login/redefinir?email=' . get('email') . '&verification_code=' . get('verification_code'), 'post', ['id' => 'redefinir'], 'multipart/form-data', true); ?>

                    <?= $form->addInput('senha', 'Input',
                        [
                            'placeholder' => 'Senha',
                            'type' => 'password',
                            'value' => $login->senha == '' ? '' : $login->senha
                        ],
                        [
                            'class' => ['col-md-12']
                        ]
                    ); ?>

                    <?= $form->addInput('repeat', 'Input',
                        [
                            'placeholder' => 'Repita a senha',
                            'type' => 'password',
                            'value' => $login->repeat == '' ? '' : $login->repeat
                        ],
                        [
                            'class' => ['col-md-12']
                        ]
                    ); ?>

                    <div class="col-md-12">
                        <label></label>
                        <div class="form-group">
                            <button id="make-login" type="submit" class="btn btn-primary btn-block">Redefinir
                                <span>
                                <img style="width: 16px; display: none;" src="./assets/images/load.gif">
                                <i class="icon-circle-right2 position-right"></i>
                            </span>
                            </button>
                        </div>
                        <div class="text-center">
                            <label style="display: none" id="error-login" class="validation-error-label"></label>
                            <br><br>
                            <a href="login/">Entrar</a>
                        </div>
                    </div>

                    <?php $form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>