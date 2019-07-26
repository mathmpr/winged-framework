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
                                <?= $form->begin((\Admin::isInsert() ? $this->insert : $this->update . uri('id')), 'post', [], 'multipart/form-data', true); ?>
                                <?php
                                if ($model->primaryKey()) {
                                    echo $form->addInput(Usuarios::primaryKeyName(), 'Input', ['type' => 'hidden']);
                                }
                                ?>
                                <div class="panel panel-flat">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">Usuário</h5>
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?=
                                                        $form->addInput('nome', 'Input',
                                                            [],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        );
                                                        ?>
                                                        <?= $form->addInput('senha', 'Input',
                                                            [
                                                                'value' => '',
                                                                'type' => 'password'
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        ); ?>
                                                        <?= $form->addInput('repeat', 'Input',
                                                            [
                                                                'value' => '',
                                                                'type' => 'password'
                                                            ],
                                                            [
                                                                'class' => ['col-md-4']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <?= $form->addInput('email', 'Input',
                                                            [],
                                                            [
                                                                'class' => [Login::currentIsAdm() ? 'col-md-4' : 'col-md-6']
                                                            ]
                                                        ); ?>

                                                        <?php

                                                        if (Login::currentIsAdm()) {
                                                            echo $form->addInput('warn_days', 'Input',
                                                                [],
                                                                [
                                                                    'class' => ['col-md-4']
                                                                ]
                                                            );
                                                        }

                                                        ?>

                                                        <?= $form->addInput('telefone', 'Input',
                                                            [],
                                                            [
                                                                'class' => [Login::currentIsAdm() ? 'col-md-4' : 'col-md-6']
                                                            ]
                                                        ); ?>
                                                    </div>
                                                </div>

                                                <?php

                                                if (Login::currentIsAdm()) {
                                                    ?>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <?= $form->addInput('tipo', 'Radio',
                                                                [
                                                                    'values' => [
                                                                        'Administrador',
                                                                        'Comum'
                                                                    ]
                                                                ],
                                                                [
                                                                    'class' => ['col-md-6']
                                                                ]
                                                            ); ?>
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
                                                    <?php

                                                    $menus = new Menu();
                                                    $menus = $menus->select()->from(['M' => Menu::tableName()])->execute();

                                                    $acitveMenus = new UsuariosPermissoesMenu();
                                                    $acitveMenus = $acitveMenus->select()
                                                        ->from(['SPM' => UsuariosPermissoesMenu::tableName()])
                                                        ->where(ELOQUENT_EQUAL, ['SPM.id_usuario' => $model->primaryKey()])
                                                        ->execute();

                                                    $activeSubmenus = new UsuariosPermissoesSubmenu();
                                                    $activeSubmenus = $activeSubmenus->select()
                                                        ->from(['SPS' => UsuariosPermissoesSubmenu::tableName()])
                                                        ->where(ELOQUENT_EQUAL, ['SPS.id_usuario' => $model->primaryKey()])
                                                        ->execute();

                                                    function getActiveMenu($key, $models)
                                                    {
                                                        return getActive($key, $models, 'id_menu');
                                                    }

                                                    function getActiveSubmenu($key, $models)
                                                    {
                                                        return getActive($key, $models, 'id_submenu');
                                                    }

                                                    function getActive($key, $models, $field)
                                                    {
                                                        $post = $_POST;
                                                        if ($models && $key) {
                                                            foreach ($models as $model) {
                                                                if ($field == 'id_menu') {
                                                                    if (array_key_exists('Winged\Model\UsuariosPermissoesMenu', $post)) {
                                                                        $post = $post['Winged\Model\UsuariosPermissoesMenu'];
                                                                        if ($key == $model->{$field} || in_array($key, $post)) {
                                                                            return 'checked="checked"';
                                                                        }
                                                                    } else {
                                                                        if ($key == $model->{$field}) {
                                                                            return 'checked="checked"';
                                                                        }
                                                                    }
                                                                } else {
                                                                    if (array_key_exists('Winged\Model\UsuariosPermissoesSubmenu', $post)) {
                                                                        $post = $post['Winged\Model\UsuariosPermissoesSubmenu'];
                                                                        if ($key == $model->{$field} || in_array($key, $post)) {
                                                                            return 'checked="checked"';
                                                                        }
                                                                    } else {
                                                                        if ($key == $model->{$field}) {
                                                                            return 'checked="checked"';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if ($field == 'id_menu') {
                                                                if (array_key_exists('Winged\Model\UsuariosPermissoesMenu', $post)) {
                                                                    $post = $post['Winged\Model\UsuariosPermissoesMenu'];
                                                                    if (in_array($key, $post)) {
                                                                        return 'checked="checked"';
                                                                    }
                                                                }
                                                            } else {
                                                                if (array_key_exists('Winged\Model\UsuariosPermissoesSubmenu', $post)) {
                                                                    $post = $post['Winged\Model\UsuariosPermissoesSubmenu'];
                                                                    if (in_array($key, $post)) {
                                                                        return 'checked="checked"';
                                                                    }
                                                                }
                                                            }

                                                        }
                                                        return '';
                                                    }

                                                    if ($menus && $model->session_namespace === 'FUN') {
                                                        ?>
                                                        <div id="userPerms" class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Permissões: </label>
                                                                    <div class="checkbox inline row">
                                                                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                                                            <input id="userPermEnableDisable"
                                                                                   value="1"
                                                                                   type="checkbox">
                                                                            <label for="userPermEnableDisable">
                                                                                <span></span>Permitir tudo / Negar tudo
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                                foreach ($menus as $key => $menu) {
                                                                    $submenus = new Submenu();
                                                                    $submenus = $submenus->select()->from(['SM' => Submenu::tableName()])
                                                                        ->where(ELOQUENT_EQUAL, ['SM.id_menu' => $menu->primaryKey()])->execute();
                                                                    ?>
                                                                    <div class="<?= count7($menus) - 1 === $key ? 'last ' : '' ?> <?= count7($submenus) === false ? 'separator ' : '' ?>col-md-12">
                                                                        <div class="checkbox inline row">
                                                                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                                                                <input <?= getActiveMenu($menu->primaryKey(), $acitveMenus) ?>
                                                                                        name="Winged\Model\UsuariosPermissoesMenu[id_menu][]"
                                                                                        id="UsuariosPermissoesMenu_id_perm_menu_<?= $key ?>"
                                                                                        value="<?= $menu->primaryKey() ?>"
                                                                                        type="checkbox">
                                                                                <label for="UsuariosPermissoesMenu_id_perm_menu_<?= $key ?>">
                                                                                    <span></span><?= $menu->nome ?>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                    if ($submenus) {
                                                                        ?>
                                                                        <div class="nexting col-md-12">
                                                                            <div class="checkbox inline row">
                                                                                <?php
                                                                                foreach ($submenus as $_key => $submenu) {
                                                                                    ?>
                                                                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                                                                        <input <?= getActiveSubmenu($submenu->primaryKey(), $activeSubmenus) ?>
                                                                                                name="Winged\Model\UsuariosPermissoesSubmenu[id_submenu][]"
                                                                                                id="UsuariosPermissoesSubmenu_id_perm_menu_<?= $key . '_' . $_key ?>"
                                                                                                value="<?= $submenu->primaryKey() ?>"
                                                                                                type="checkbox">
                                                                                        <label for="UsuariosPermissoesSubmenu_id_perm_menu_<?= $key . '_' . $_key ?>">
                                                                                            <span></span><?= $submenu->nome ?>
                                                                                        </label>
                                                                                    </div>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }

                                                }

                                                ?>
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
