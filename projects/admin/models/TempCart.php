<?php

use Winged\Model\Model;
use Winged\Date\Date;

/**
 * Class TempCart
 */
class TempCart extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_carrinho integer */
    public $id_carrinho;

    /** @var $id_usuario integer */
    public $id_usuario;

    /** @var $id_cliente integer */
    public $id_cliente;

    /** @var $id_bairro integer */
    public $id_bairro;

    /** @var $valor_frete integer */
    public $valor_frete;

    /** @var $endereco string */
    public $endereco;

    /** @var $agendamento string */
    public $agendamento;

    /** @var $metodo_pagamento string */
    public $metodo_pagamento;

    /** @var $observacoes string */
    public $observacoes;

    /** @var $innf string */
    public $innf;

    /**
     * @return string
     */
    public static function tableName()
    {
        return "temp_cart";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_carrinho";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_carrinho = $pk;
            return $this;
        }
        return $this->id_carrinho;
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
        return [
            'agendamento' => function () {
                if ($this->agendamento != null) {
                    return (new Date($this->agendamento))->dmy();
                }
            }
        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }
}