<?php

use Winged\Controller\Controller;
use Winged\Winged;
use Winged\Http\Session;
use Winged\Http\Cookie;

/**
 * Class NewsCategoriasController
 */
class NewsCategoriasController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->dynamic('active_page_group', 'news');
        $this->dynamic('page_name', 'Categorias das notícias');
        $this->dynamic('page_action_string', 'Listando');

        $this->dynamic('list', 'news_categorias/');
        $this->dynamic('insert', 'news_categorias/insert/');
        $this->dynamic('update', 'news_categorias/update/');
    }

    public function actionIndex()
    {
        $this->redirectTo(Winged::$page_surname . '/page/1');
    }

    public function actionPage()
    {

        AdminAssets::init($this);

        $this->setNicknamesToUri(['page']);

        $limit = get('limit') ? get('limit') : 10;

        $page = uri('page') ? uri('page') : 1;

        $model = new NewsCategorias();

        $success = false;
        if (intval(Session::get('action')) > 0) {
            $success = true;
        }

        Session::remove('action');

        $links = 3;

        $model->select()->from(['CATEGORIAS' => 'news_categorias']);

        Admin::buildSearchModel($model, [
            'CATEGORIAS.categoria',
            'CATEGORIAS.slug',
        ]);

        Admin::buildOrderModel($model, [
            'sort_categoria' => 'CATEGORIAS.categoria',
            'sort_slug' => 'CATEGORIAS.slug',
        ]);

        $paginate = new Paginate($model->count(), $model);
        $data = $paginate->getData($limit, $page);
        $links = $paginate->createLinks($links);

        $this->html(Winged::$page_surname . '/' . Winged::$page_surname . '/_index', [
            'success' => $success,
            'models' => $data->data,
            'links' => $links,
        ]);
    }

    public function actionInsert()
    {
        $this->dynamic('page_action_string', 'Inserindo');
        AdminAssets::init($this);
        $model = new NewsCategorias();
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
        $model = new NewsCategorias();
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
        $this->dynamic('page_action_string', 'Alterando');
        AdminAssets::init($this);
        $model = new NewsCategorias();
        $this->setNicknamesToUri(['id']);
        if (uri('id') !== false && is_get()) {
            $model->autoLoadDb(uri('id'));
            if ($model->primaryKey()) {
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

    /**
     * @return array
     */
    public function save()
    {
        $model = (new NewsCategorias())->load($_POST);
        if ($model->validate() && ($model->save())) {
            Slugs::pushSlug($model->categoria, $model->primaryKey(), NewsCategorias::tableName());
            $action = Session::get('action');
            Session::always('action', $model->primaryKey());
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

    /**
     * get news_categorias as array
     *
     * @return array|bool
     */
    public function actionCategorias()
    {
        if (is_post()) {
            $get = (new NewsCategorias())
                ->select(['NC.id_categoria', 'NC.categoria'])
                ->from(['NC' => NewsCategorias::tableName()])
                ->where(ELOQUENT_LIKE, ['LCASE(NC.categoria)' => '%' . mb_strtolower(post('query'), 'UTF-8') . '%'])
                ->execute(true);

            if ($get) {
                return $get;
            }
        }
        return [];
    }
}