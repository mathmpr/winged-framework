<?php

use Winged\Controller\Controller;
use Winged\Database\DbDict;
use Winged\Model\Login;
use Winged\Model\Cidades;
use Winged\Model\Bairros;
use Winged\Model\ProdutosCategorias;

class ServicesController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
    }
    public function actionGetCidades()
    {
        $model = (new Cidades())
            ->select()
            ->from(['CIDADES' => 'cidades'])
            ->orderBy(ELOQUENT_ASC, 'CIDADES.cidade');
        if (postset('id_estado')) {
            $model->where(ELOQUENT_EQUAL, ['CIDADES.id_estado' => post('id_estado')]);
        }
        return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'cidade', 'id_cidade')];
    }
    public function actionGetBairros()
    {
        $model = (new Bairros())
            ->select()
            ->from(['BAIRROS' => 'bairros'])
            ->orderBy(ELOQUENT_ASC, 'BAIRROS.nome');
        if (postset('id_estado')) {
            $model->where(ELOQUENT_EQUAL, ['BAIRROS.id_estado' => post('id_estado')]);
            if (postset('id_cidade')) {
                $model->andWhere(ELOQUENT_EQUAL, ['BAIRROS.id_cidade' => post('id_cidade')]);
                return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'nome', 'id_bairro')];
            }
        }
        return ['status' => false, 'data' => []];
    }
    public function actionGetSubcategorias()
    {
        $model = (new ProdutosSubcategorias())
            ->select()
            ->from(['SUBCATEGORIAS' => 'produtos_subcategorias'])
            ->orderBy(ELOQUENT_ASC, 'SUBCATEGORIAS.nome');
        if (postset('id_categoria')) {
            $model->where(ELOQUENT_EQUAL, ['SUBCATEGORIAS.id_categoria' => post('id_categoria')]);
        }
        return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'nome', 'id_subcategoria')];
    }
}