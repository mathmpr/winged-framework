<?php

use Winged\Model\Model;

/**
 * Class Cidades
 *
 * @package Winged\Model
 */
class Cidades extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_cidade integer */
    public $id_cidade;

    /** @var $id_estado integer */
    public $id_estado;

    /** @var $cidade string */
    public $cidade;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "cidades";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_cidade";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_cidade = $pk;
            return $this;
        }
        return $this->id_cidade;
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
            'cidade' => 'Nome da cidade: ',
            'id_estado' => 'Estado em que está localizada: ',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'cidade' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'id_estado' => [
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
            'id_estado' => [
                'required' => true,
            ],
            'cidade' => [
                'required' => true,
            ]
        ];
    }
}