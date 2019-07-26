<?php

use Winged\Model\Model;

/**
 * Class Bairros
 */
class Bairros extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_bairro integer */
    public $id_bairro;

    /** @var $id_cidade integer */
    public $id_cidade;

    /** @var $id_estado integer */
    public $id_estado;

    /** @var $nome string */
    public $nome;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "bairros";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_bairro";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_bairro = $pk;
            return $this;
        }
        return $this->id_bairro;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [];
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
    public function labels()
    {
        return [
            'nome' => 'Nome do bairro: ',
            'id_cidade' => 'Cidade em que está localizado: ',
            'id_estado' => 'Estado em que está localizado: ',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'nome' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'id_estado' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'id_cidade' => [
                'required' => 'Esse campo é obrigatório',
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'nome' => [
                'required' => true,
            ],
            'id_estado' => [
                'required' => true,
            ],
            'id_cidade' => [
                'required' => true,
            ]
        ];
    }
}