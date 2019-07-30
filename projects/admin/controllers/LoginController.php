<?php

use Winged\Controller\Controller;

use Winged\Winged;
use Winged\Database\DbDict;

class LoginController extends Controller
{
    /**
     * @var $seo_obj null | \Winged\Model\SeoConfig
     */
    public $seo_obj = null;

    /**
     * @var $seo null | string
     */
    public $seo = null;

    public function __construct()
    {
        parent::__construct();
        /**
         * @var $seo SeoConfig;
         */
        $seo = (new SeoConfig())->findOne(1);
        $exp = explode(' ', $seo->site_name);
        $name = '';
        $x = 0;
        foreach ($exp as $nam) {
            $name .= trim($nam)[0];
            $x++;
            if ($x === 2) {
                break;
            }
        }
        $this->dynamic('seo', $name);
        $this->dynamic('seo_obj', $seo);
    }

    public function actionIndex()
    {
        if (Login::permission()) {
            $this->redirectTo('default');
        }
        AdminAssets::init($this);
        $this->appendBodyClass('login-container');
        $this->appendJs("login", Winged::$parent . "assets/js/pages/login.js");
        $this->html(Winged::$page_surname . '/' . Winged::$page_surname, [
            'message' => getset('email') ? '<br><p>Sua senha foi alterada com sucesso. Agora você pode fazer login normalmente usando o e-mail <b>' . get('email') . '.</b></p>' : ''
        ]);
    }

    public function actionRedefinir()
    {
        if (Login::permission()) {
            $this->redirectTo('default');
        }

        $message = '<br><p>Redefina sua senha abaixo. Para isso, os campos precisam ser iguais e conter no mínimo 6 caracteres.</p>';

        AdminAssets::init($this);
        $this->body_class = 'login-container';
        $this->appendJs("login", "./projects/admin/assets/js/pages/login.js");

        if (is_get()) {
            if (getset('email') && getset('verification_code')) {
                $model = new Login();
                $pop = $model
                    ->select()
                    ->from(['USUARIO' => Usuarios::tableName()])
                    ->where(ELOQUENT_EQUAL, ['USUARIO.email' => get('email')])
                    ->andWhere(ELOQUENT_EQUAL, ['USUARIO.senha' => get('verification_code')])
                    ->one();
                if (!$pop) {
                    $message = '<br><p>Infelizmente o código de verificação ou o e-mail já não coferém. Preencha com o e-mail e tente fazer a redefinição de senha novamente.</p>';
                    $this->html(Winged::$page_surname . '/forgot', [
                        'message' => $message
                    ]);
                } else {
                    $this->html(Winged::$page_surname . '/redefinir', [
                        'message' => $message
                    ]);
                }
            } else {
                $this->redirectTo('login');
            }
        } else if (is_post()) {
            if (getset('email') && getset('verification_code')) {
                $model = new Login();
                $pop = $model
                    ->select()
                    ->from(['USUARIO' => Usuarios::tableName()])
                    ->where(ELOQUENT_EQUAL, ['USUARIO.email' => get('email')])
                    ->andWhere(ELOQUENT_EQUAL, ['USUARIO.senha' => get('verification_code')])
                    ->one();
                if (!$pop) {
                    $message = '<br><p>Infelizmente o código de verificação ou o e-mail já não coferém. Não conseguimos alterar sua senha. Tente fazer a recuperação novamente.</p>';
                    $this->html(Winged::$page_surname . '/forgot', [
                        'message' => $message
                    ]);
                } else {
                    $model = new Login();
                    $model->load($_POST);
                    if ($model->repeat != $model->senha) {
                        $message = '<br><p>Esperto! De alguma forma as senhas não conferem. Preencha os dois campos de forma semelhante.</p>';
                        $this->html(Winged::$page_surname . '/redefinir', [
                            'message' => $message
                        ]);
                    } else {
                        $pop->load([
                            'Login' => [
                                'senha' => $_POST[get_class($pop)]['senha']
                            ]
                        ]);
                        $pop->save();
                        $this->redirectTo('login?email=' . $pop->email, false);
                    }
                }
            }
        }

    }

    public function actionForgot()
    {
        if (Login::permission()) {
            $this->redirectTo('default');
        }

        $message = '<br><p>Digite o e-mail que custuma utilizar e clique em recuperar. Enviaremos um e-mail com as instruções de recuperação de senha.</p>';

        if (is_get()) {
            AdminAssets::init($this);
            $this->body_class = 'login-container';
            $this->appendJs("login", "./projects/admin/assets/js/pages/login.js");
            $this->html(Winged::$page_surname . '/forgot', [
                'message' => $message
            ]);
        } else {
            $model = new Login();
            $model->load($_POST);
            $pop = $model
                ->select()
                ->from(['USUARIO' => Usuarios::tableName()])
                ->where(ELOQUENT_EQUAL, ['USUARIO.email' => $model->email])
                ->one();
            if ($pop) {

                $pop->load([
                    'Login' => [
                        'senha' => md5(\Winged\Utils\RandomName::generate())
                    ]
                ]);

                $pop->save();

                $html = '<p>Para redifinir sua senha <a href="' . Winged::$protocol . 'admin/login/redefinir?email=' . $pop->email . '&verification_code=' . $pop->senha . '">clique aqui</a>.</p>';

                $mailgun = new Mailgun('24c2bc3cf8f31334010eaf804ee30546-b0aac6d0-491086f4', 'pradoit.com.br');

                $mailgun->sendMessage([
                    'from' => $this->seo_obj->site_name . ' <mailgun@pradoit.com.br>',
                    'to' => $pop->nome . ' <' . $pop->email . '>',
                    'subject' => 'Redefina sua senha do Dashboard ' . $this->seo_obj->site_name,
                    'html' => $html,
                    'o:tracking' => 'yes',
                    'o:tracking-clicks' => 'yes',
                    'o:tracking-opens' => 'yes',
                    'o:skip-verification' => false
                ]);

                AdminAssets::init($this);
                $this->body_class = 'login-container';
                $this->appendJs("login", "./projects/admin/assets/js/pages/login.js");
                $this->html(Winged::$page_surname . '/' . Winged::$page_surname, [
                    'message' => '<br><p>Enviamos um e-mail para <b>' . $pop->email . '</b> com um link para redefinir sua senha.</p>'
                ]);
            } else {
                $message = '<br><p>Não encontramos nenhuma conta ligada a este e-mail. Se esqueceu o seu e-mail, entre em contato desenvolvedor ou agência para recuperar suas credenciais.</p>';
                AdminAssets::init($this);
                $this->body_class = 'login-container';
                $this->appendJs("login", "./projects/admin/assets/js/pages/login.js");
                $this->html(Winged::$page_surname . '/forgot', [
                    'message' => $message
                ]);
            }
        }
    }

    public function actionLogin()
    {
        if (is_post()) {
            $model = new Login();
            $model->load($_POST);
            $pop = $model
                ->select()
                ->from(['USUARIO' => Usuarios::tableName()])
                ->where(ELOQUENT_EQUAL, ['USUARIO.email' => $model->email])
                ->andWhere(ELOQUENT_EQUAL, ['USUARIO.senha' => $model->senha])
                ->one();
            if ($pop) {
                $pop->initSession();
                if ($pop->permission()) {
                    return ['status' => true];
                }
            }
            return ['status' => false, 'message' => 'Usuário não encontrado.'];
        }
    }

    public function actionLogout()
    {
        Login::destroySession();
        $this->redirectTo();
    }
}