<?php

use Winged\Controller\Controller;
use Winged\Http\Session;
use Winged\Winged;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'default');
        $this->dynamic('page_name', 'SEO Config');
        $this->dynamic('page_action_string', 'SEO Config');
    }

    public function actionIndex()
    {
        AdminAssets::init($this);

        $success = false;

        Session::always('action', 'update');

        if (is_post()) {
            $model = new SeoConfig();
            $model->load($_POST);
            if ($model->validate()) {
                $model->save();
                $success = true;
            }
        } else {
            $model = (new SeoConfig())->findOne(1);
        }

        if (!$model) {
            (new SeoConfig())->delete()->delegate();
            (new SeoConfig())->load([
                'SeoConfig' => [
                    'seo_id' => 1
                ]
            ])->insert();
            $model = (new SeoConfig())->findOne(1);
        }

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'model' => $model,
            'success' => $success,
        ]);

    }
}