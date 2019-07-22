<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Model\Login;
use Winged\Model\Usuarios;
use Winged\Http\Session;
use Winged\Http\Cookie;
use Winged\Model\UsuariosPermissoesMenu;
use Winged\Model\UsuariosPermissoesSubmenu;
use Winged\Database\DbDict;

class UsuariosController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        !Login::permission() ? $this->redirectTo() : null;
        $this->dynamic('active_page_group', 'usuarios');
        $this->dynamic('page_name', 'Usuários');
        $this->dynamic('page_action_string', 'Listando');
        $this->dynamic('list', 'usuarios/');
        $this->dynamic('insert', 'usuarios/insert/');
        $this->dynamic('update', 'usuarios/update/');
    }

    public function actionIndex()
    {
        !Login::permissionAdm() ? $this->redirectTo() : null;
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionPage()
    {
        AdminAssets::init($this);
        $this->setNicknamesToUri(['page']);
        $limit = get('limit') ? get('limit') : 10;
        $page = uri('page') ? uri('page') : 1;
        $model = new Usuarios();
        $success = null;
        if (!in_array(Session::get('action'), ['insert', 'update']) && Session::get('action') !== false) {
            $success = $model->findOne(Session::get('action'));
        }
        $success = false;
        if (intval(Session::get('action')) > 0) {
            $success = true;
        }
        Session::remove('action');
        $links = 1;
        $model->select()->from(['USUARIOS' => 'usuarios']);

        \Admin::buildSearchModel($model, [
            'USUARIOS.nome',
            'USUARIOS.email',
            'USUARIOS.tipo',
            'USUARIOS.status',
            'USUARIOS.data_cadastro'
        ]);
        \Admin::buildOrderModel($model, [
            'sort_nome' => 'USUARIOS.nome',
            'sort_email' => 'USUARIOS.email',
            'sort_tipo' => 'USUARIOS.tipo',
            'sort_status' => 'USUARIOS.status',
            'sort_data_cadastro' => 'USUARIOS.data_cadastro',
        ]);
        $paginate = new Paginate($model->count(), $model);
        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links, Winged::$page_surname);
        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'success' => $success,
            'models' => $data->data,
            'links' => $links,
        ]);
    }

    public function actionInsert()
    {
        !Login::permissionAdm() ? $this->redirectTo() : null;
        $this->dynamic('page_action_string', 'Inserindo');
        AdminAssets::init($this);
        $this->appendJs('usuarios', Winged::$parent . 'assets/js/pages/usuarios.js');
        $this->appendJs('_mask', '<script> $(function(){ $("#Usuarios_telefone").mask("(99) 99999-999?9") }) </script>');
        $model = new Usuarios();
        Session::always('action', 'insert');
        if (is_get()) {
            $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                'model' => $model
            ]);
        } else if (is_post()) {
            $save = $this->save();
            if (!$save['status']) {
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                ]);
            }
        }
    }

    public function actionDelete()
    {
        !Login::permissionAdm() ? $this->redirectTo() : null;
        $model = new Usuarios();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            $model->remove();
        }
        if (($to = Cookie::get('from_url'))) {
            Cookie::remove('from_url');
            $this->redirectOnly($to);
        } else {
            $this->redirectTo(Winged::$page_surname);
        }
    }

    public function actionUpdate()
    {
        !Login::permission() ? $this->redirectTo('../') : null;
        $this->dynamic('page_action_string', 'Alterando');
        AdminAssets::init($this);
        $this->appendJs('usuarios', Winged::$parent . 'assets/js/pages/usuarios.js');
        $this->appendJs('_mask', '<script> $(function(){ $("#Usuarios_telefone").mask("(99) 9999-9999?9") }) </script>');
        $model = new Usuarios();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            if ($model->primaryKey()) {
                if ($model->primaryKey() != Login::current()->primaryKey()) {
                    !Login::permissionAdm() ? $this->redirectTo() : null;
                }
                Session::always('action', 'update');
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $model
                ]);
            } else {
                if (($to = Cookie::get('from_url'))) {
                    Cookie::remove('from_url');
                    $this->redirectOnly($to);
                } else {
                    $this->redirectTo(Winged::$page_surname);
                }
            }
        } else if (is_post()) {
            $save = $this->save();
            if (!$save['status']) {
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_form', [
                    'model' => $save['model'],
                ]);
            }
        }
    }

    public function save()
    {
        $model = (new Usuarios())->load($_POST);
        if ($model->validate() && ($id = $model->save())) {

            if (Login::currentIsAdm()) {
                $permMenus = new UsuariosPermissoesMenu();
                $permSubmenus = new UsuariosPermissoesSubmenu();

                $permMenus->id_usuario = $model->id_usuario;
                $permSubmenus->id_usuario = $model->id_usuario;

                $permMenus->delete(['PM' => UsuariosPermissoesMenu::tableName()])->where(ELOQUENT_EQUAL, ['PM.id_usuario' => $permMenus->id_usuario])->execute();
                $permSubmenus->delete(['PM' => UsuariosPermissoesSubmenu::tableName()])->where(ELOQUENT_EQUAL, ['PM.id_usuario' => $permSubmenus->id_usuario])->execute();

                $permMenus = $permMenus->loadMultiple($_POST);
                $permSubmenus = $permSubmenus->loadMultiple($_POST);
                if ($permMenus) {
                    foreach ($permMenus as $key => $permMenu) {
                        $permMenus[$key]->load([
                            'UsuariosPermissoesMenu' => [
                                'id_usuario' => $model->id_usuario
                            ]
                        ])->save();
                    }
                }

                if ($permSubmenus) {
                    foreach ($permSubmenus as $key => $permSubmenu) {
                        $permSubmenus[$key]->load([
                            'UsuariosPermissoesSubmenu' => [
                                'id_usuario' => $model->id_usuario
                            ]
                        ])->save();
                    }
                }
            }

            $action = Session::get('action');
            Session::always('action', $id);
            if (($to = Cookie::get('from_url')) && $action == 'update') {
                Cookie::remove('from_url');
                $this->redirectOnly($to);
            } else {
                $this->redirectTo(Winged::$page_surname);
            }
        } else {
            return ['status' => false, 'model' => $model];
        }
    }
}