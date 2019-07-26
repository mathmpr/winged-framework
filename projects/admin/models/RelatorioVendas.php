<?php

use Winged\Validator\Validator;
use Winged\Date\Date;

/**
 * Class RelatorioVendas
 *
 * @package Winged\Model
 */
class RelatorioVendas extends \Produtos
{

    /**
     * @var $data_inicial string.
     */
    public $data_inicial;

    /**
     * @var $data_final string.
     */
    public $data_final;

    /**
     * @var $clientes array.
     */
    public $clientes;

    /**
     * @var $bairros array.
     */
    public $bairros;

    /**
     * @var $produtos array.
     */
    public $produtos;

    /**
     * @var $status array.
     */
    public $status;

    /**
     * RelatorioVendas constructor.
     */
    public function __construct()
    {
        setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
        parent::__construct();
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'produtos' => 'Com os seguintes produtos: ',
            'bairros' => 'Nos bairros: ',
            'clientes' => 'Que contenham os clientes: ',
            'data_inicial' => 'Começando da data: ',
            'data_final' => 'Até a data: ',
            'status' => 'Com o(s) statu(s): ',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'data_inicial' => function () {
                return Date::valid($this->data_inicial) ? $this->data_inicial : (new Date())->dmy();
            },
            'data_final' => function () {
                return Date::valid($this->data_final) ? $this->data_final : (new Date())->dmy();
            },
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
            'clientes' => [
                'safe' => 'safe'
            ],
            'bairros' => [
                'safe' => 'safe'
            ],
            'produtos' => [
                'safe' => 'safe'
            ],
            'status' => [
                'safe' => 'safe'
            ],
            'data_inicial' => [
                'safe' => 'safe',
                'required' => true,
                'greater' => !(new Validator())->dateGreater($this->data_inicial, $this->data_final),
                'eq' => function () {
                    return $this->data_inicial == $this->data_final ? false : true;
                }
            ],
            'data_final' => [
                'safe' => 'safe',
                'required' => true,
                'greater' => !(new Validator())->dateSmaller($this->data_final, $this->data_inicial),
                'eq' => function () {
                    return $this->data_inicial == $this->data_final ? false : true;
                }
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'data_inicial' => [
                'required' => 'Esse campo é obrigátorio.',
                'greater' => 'A data inicial não pode ser maior que a data final.',
                'eq' => 'As datas de ínicio e fim não podem ser iguais.'
            ],
            'data_final' => [
                'required' => 'Esse campo é obrigátorio.',
                'greater' => 'A data final não pode ser menor que a data inicial.',
                'eq' => 'As datas de ínicio e fim não podem ser iguais.'
            ],
        ];
    }

}