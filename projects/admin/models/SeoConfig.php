<?php

use Winged\Model\Model;

/**
 * Class SeoConfig
 */
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

    /** @var $site_name integer */
    public $site_name;

    /** @var $author integer */
    public $author;

    /** @var $publisher integer */
    public $publisher;

    /** @var $language integer */
    public $language;

    /** @var $robots integer */
    public $robots;

    /** @var $revisit_after integer */
    public $revisit_after;

    /** @var $location integer */
    public $location;

    /** @var $geo_position integer */
    public $geo_position;

    /** @var $geo_placename integer */
    public $geo_placename;

    /** @var $geo_region integer */
    public $geo_region;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "seo_config";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_seo";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_seo = $pk;
            return $this;
        }
        return $this->id_seo;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ga_status' => function(){
                if(!$this->loaded('ga_status')){
                    return 0;
                }
            },
            'append_seo' => function(){
                if(!$this->loaded('append_seo')){
                    return 0;
                }
            }
        ];
    }

    /**
     * @return array
     */
    public function reverseBehaviors()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'uid' => [
                'required' => true,
                'isga' => function () {
                    return preg_match('/^ua-\d{4,9}-\d{1,4}$/i', strval($this->uid)) ? true : false;
                }
            ],
            'site_name' => [
                'required' => true
            ],
            'author' => [
                'required' => true
            ],
            'publisher' => [
                'required' => true
            ],
            'language' => [
                'required' => true
            ],
            'robots' => [
                'required' => true
            ],
            'revisit_after' => [
                'required' => true
            ],
            'location' => [
                'required' => true
            ],
            'geo_position' => [
                'required' => true
            ],
            'geo_placename' => [
                'required' => true
            ],
            'geo_region' => [
                'required' => true
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'uid' => [
                'required' => 'Esse campo é obrigatório.',
                'isga' => 'Esse ID de Acompanhamento não é um ID válido.'
            ],
            'geo_region' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'site_name' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'author' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'publisher' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'language' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'robots' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'revisit_after' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'location' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'geo_position' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'geo_placename' => [
                'required' => 'Esse campo é obrigatório.',
            ],
        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'ga_status' => 'Habilitar / Desabilitar a incusão do Google Analitycs nas páginas do site: ',
            'uid' => 'ID de Acompanhamento do Google Analitycs: ',
            'append_seo' => 'Adicionar CSS e JavaScript úteis para dectar possiveis erros com SEO: ',
            'site_name' => 'Nome principal do site: ',
            'author' => 'Autor do site: ',
            'publisher' => 'Publicante do site: ',
            'language' => 'Lingua padrão do site [ Em inglês. Exemplo: Portuguese ]: ',
            'robots' => 'Comandos para o robots [ Indexação ]: ',
            'revisit_after' => 'Comandos para o robots [ Revisitar o site ]: ',
            'location' => 'Cidade, Pais [ Exemplo: Londrina, Brasil ]: ',
            'geo_position' => 'Latitude, Longitude [ Exemplo: -23.3103198, -51.1648426 ]: ',
            'geo_placename' => 'Estado, Cidade (Exemplo: Paraná, Londrina): ',
            'geo_region' => 'Exemplo: BR-PR: '
        ];
    }
}