<?php

use Winged\Model\Model;
use Winged\Date\Date;

/**
 * Class EnderecosClientes
 */
class EnderecosClientes extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_endereco integer */
    public $id_endereco;

    /** @var $id_cidade integer */
    public $id_cidade;

    /** @var $id_estado integer */
    public $id_estado;

    /** @var $id_bairro integer */
    public $id_bairro;

    /** @var $id_cliente integer */
    public $id_cliente;

    /** @var $endereco string */
    public $endereco;

    /** @var $numero string */
    public $numero;

    /** @var $codigo_postal string */
    public $codigo_postal;

    /** @var $complemento string */
    public $complemento;

    /** @var $principal integer */
    public $principal;

    /** @var $data_cadastro integer */
    public $data_cadastro;


    /**
     * @return string
     */
    public static function tableName()
    {
        return "enderecos_clientes";
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_endereco";
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_endereco = $pk;
            return $this;
        }
        return $this->id_endereco;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'data_cadastro' => function () {
                if (\Admin::isInsert()) {
                    return Date::now()->sql();
                }
            },
            'principal' => function () {
                if ($this->loaded('principal') === false) {
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
        return [
            'data_cadastro' => function () {
                return (new Date($this->data_cadastro))->dmy();
            },
        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'endereco' => 'Logradouro: ',
            'numero' => 'Número: ',
            'codigo_postal' => 'Código postal: ',
            'complemento' => 'Complemento: ',
            'id_bairro' => 'Bairro: ',
            'principal' => 'É o endereço primario do cliente?: ',
            'id_cidade' => 'Cidade: ',
            'id_estado' => 'Estado: ',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'id_estado' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'id_cidade' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'id_bairro' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'endereco' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'numero' => [
                'required' => 'Esse campo é obrigatório',
            ],
            'codigo_postal' => [
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
            'id_cidade' => [
                'required' => true,
            ],
            'id_bairro' => [
                'required' => true,
            ],
            'endereco' => [
                'required' => true,
            ],
            'numero' => [
                'required' => true,
            ],
            'codigo_postal' => [
                //'required' => true,
            ],
        ];
    }
}