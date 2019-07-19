<?php

namespace Winged\Model;

use Winged\Http\Session;
use Winged\Date\Date;
use Winged\Database\DbDict;
use Winged\Validator\Validator;

class Clientes extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }
    /** @var $id_cliente integer */
    public $id_cliente;
    /** @var $id_usuario integer */
    public $id_usuario;
    /** @var $nome string */
    public $nome;
    /** @var $nome_fantasia string */
    public $nome_fantasia;
    /** @var $razao_social string */
    public $razao_social;
    /** @var $inscricao_estadual string */
    public $inscricao_estadual;
    /** @var $email string */
    public $email;
    /** @var $cnpj string */
    public $cnpj;
    /** @var $cpf string */
    public $cpf;
    /** @var $rg string */
    public $rg;
    /** @var $data_cadastro string */
    public $data_cadastro;
    /** @var $status integer */
    public $status;
    /** @var $telefone string */
    public $telefone;
    public static function tableName()
    {
        return "clientes";
    }
    public static function primaryKeyName()
    {
        return "id_cliente";
    }
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_cliente = $pk;
            return $this;
        }
        return $this->id_cliente;
    }
    public function behaviors()
    {
        return [
            'status' => function () {
                if ($this->loaded('status') === false) {
                    return 0;
                }
            },
            'data_cadastro' => function () {
                if (Session::get('action') == 'insert') {
                    return Date::now()->sql();
                }
            },
        ];
    }
    public function reverseBehaviors()
    {
        return [
            'data_cadastro' => function () {
                return (new Date($this->data_cadastro))->dmy();
            },
        ];
    }
    public function labels()
    {
        return [
            'nome' => 'Responsável: ',
            'nome_fantasia' => 'Nome fantasia: ',
            'razao_social' => 'Razão social',
            'inscricao_estadual' => 'Inscrição estadual: ',
            'email' => 'E-mail: ',
            'cnpj' => 'CNPJ (se for pessoa jurídica): ',
            'cpf' => 'CPF (se for pessoa física): ',
            'rg' => 'RG do responsável: ',
            'status' => 'Ativo / Inativo: ',
            'telefone' => 'Telefone: ',
        ];
    }
    public function rules()
    {
        return [
            'nome' => [
                'required' => true,
            ],
            'nome_fantasia' => [
                //'required' => true,
            ],
            'razao_social' => [
                //'required' => true,
            ],
            'inscricao_estadual' => [
                //'required' => true,
            ],
            'email' => [
                'required' => true,
                'email' => true,
                'db' => function () {
                    if (Session::get('action') == 'insert') {
                        $model = (new Clientes())
                            ->select()
                            ->from(['CLIENTES' => 'clientes'])
                            ->where(ELOQUENT_EQUAL, ['CLIENTES.email' => $this->email])
                            ->one();
                    } else {
                        $model = (new Clientes())
                            ->select()
                            ->from(['CLIENTES' => 'clientes'])
                            ->where(ELOQUENT_EQUAL, ['CLIENTES.email' => $this->email])
                            ->andWhere(ELOQUENT_DIFFERENT, ['CLIENTES.' . Clientes::primaryKeyName() => $this->primaryKey()])
                            ->one();
                    }
                    if ($model) {
                        return false;
                    }
                }
            ],
            'cpf' => [
                'required' => $this->cnpj !== '' ? false : true,
                'cpf' => function () {
                    return $this->cnpj === '' ? (new Validator())->cpf($this->cpf) : true;
                },
                'db' => function () {
                    if($this->cnpj === ''){
                        if (Session::get('action') == 'insert') {
                            $model = (new Clientes())
                                ->select()
                                ->from(['CLIENTES' => 'clientes'])
                                ->where(ELOQUENT_EQUAL, ['CLIENTES.cpf' => $this->cpf])
                                ->one();
                        } else {
                            $model = (new Clientes())
                                ->select()
                                ->from(['CLIENTES' => 'clientes'])
                                ->where(ELOQUENT_EQUAL, ['CLIENTES.cpf' => $this->cpf])
                                ->andWhere(ELOQUENT_DIFFERENT, ['CLIENTES.' . Clientes::primaryKeyName() => $this->primaryKey()])
                                ->one();
                        }
                        if ($model) {
                            return false;
                        }
                    }else{
                        return true;
                    }
                }
            ],
            'cnpj' => [
                'required' => $this->cpf !== '' ? false : true,
                'cnpj' => function () {
                    return $this->cpf === '' ? (new Validator())->cnpj($this->cnpj) : true;
                },
                'db' => function () {
                    if($this->cpf === ''){
                        if (Session::get('action') == 'insert') {
                            $model = (new Clientes())
                                ->select()
                                ->from(['CLIENTES' => 'clientes'])
                                ->where(ELOQUENT_EQUAL, ['CLIENTES.cnpj' => $this->cnpj])
                                ->one();
                        } else {
                            $model = (new Clientes())
                                ->select()
                                ->from(['CLIENTES' => 'clientes'])
                                ->where(ELOQUENT_EQUAL, ['CLIENTES.cnpj' => $this->cnpj])
                                ->andWhere(ELOQUENT_DIFFERENT, ['CLIENTES.' . Clientes::primaryKeyName() => $this->primaryKey()])
                                ->one();
                        }
                        if ($model) {
                            return false;
                        }
                    }else{
                        return true;
                    }
                }
            ],
        ];
    }
    public function messages()
    {
        return [
            'nome' => [
                'required' => 'Esse campo é obrigatório.',
            ],
            'nome_fantasia' => [
                'required' => 'Esse campo é obrigatório'
            ],
            'razao_social' => [
                'required' => 'Esse campo é obrigatório'
            ],
            'inscricao_estadual' => [
                'required' => 'Esse campo é obrigatório'
            ],
            'email' => [
                'required' => 'Esse campo é obrigatório',
                'email' => 'Insira um e-mail válido',
                'db' => 'Outro cliente está usando esté e-mail',
            ],
            'cpf' => [
                'required' => 'Esse campo é obrigatório',
                'cpf' => 'CPF inválido',
                'db' => 'Outro cliente está usando esté CPF',
            ],
            'cnpj' => [
                'required' => 'Esse campo é obrigatório',
                'cnpj' => 'CNPJ inválido',
                'db' => 'Outro cliente está usando esté CNPJ',
            ],
        ];
    }
}