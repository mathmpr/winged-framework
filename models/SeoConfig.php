<?php

class SeoConfig extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_seo integer */
    public $id_seo;
    /** @var $uid string */
    public $uid;
    /** @var $ga_status integer */
    public $ga_status;
    /** @var $append_seo integer */
    public $append_seo;

    public static function tableName()
    {
        return "seo_config";
    }

    public static function primaryKeyName()
    {
        return "id_seo";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_seo = $pk;
            return $this;
        }
        return $this->id_seo;
    }

    public function behaviors()
    {
        return [];
    }

    public function reverseBehaviors()
    {
        return [];
    }

    public function rules()
    {
        return [
            'uid' => [
                'required' => true,
                'isga' => function () {
                    return preg_match('/^ua-\d{4,9}-\d{1,4}$/i', strval($this->uid)) ? true : false;
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'uid' => [
                'required' => 'Esse campo é obrigatório.',
                'isga' => 'Esse ID de Acompanhamento não é um ID válido.'
            ]
        ];
    }

    public function labels()
    {
        return [
            'ga_status' => 'Habilitar / Desabilitar a incusão do Google Analitycs nas páginas do site: ',
            'uid' => 'ID de Acompanhamento do Google Analitycs: ',
            'append_seo' => 'Adicionar CSS e JavaScript úteis para dectar possiveis erros com SEO: '
        ];
    }
}