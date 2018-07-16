<?php

use Winged\Validator\Validator;
use Winged\Http\Session;
use Winged\Database\DbDict;
use Winged\Date\Date;
use Winged\Model\Model;

/**
 * class Usuarios
 * @package Winged\Model
 **/
class Usuarios extends Model
{
    /**
     * Usuarios constructor.
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_usuario integer */
    public $id_usuario;
    /** @var $nome string */
    public $nome;
    /** @var $email string */
    public $email;
    /** @var $senha string */
    public $senha;
    /** @var $repeat string */
    public $repeat;
    /** @var $senha_antiga string */
    public $senha_antiga;
    /** @var $tipo integer */
    public $tipo;
    /** @var $data_cadastro string */
    public $data_cadastro;
    /** @var $session_namespace string */
    public $session_namespace;
    /** @var $status integer */
    public $status;
    /** @var $telefone string */
    public $telefone;
    /** @var $celular string */
    public $celular;
    /** @var $cpf string */
    public $cpf;
    /** @var $cnpj string */
    public $cnpj;
    /** @var $cep string */
    public $cep;
    /** @var $cidade integer */
    public $cidade;
    /** @var $estado integer */
    public $estado;
    /** @var $rua string */
    public $rua;
    /** @var $numero string */
    public $numero;
    /** @var $bairro integer */
    public $bairro;
    /** @var $complemento string */
    public $complemento;
    /** @var $nome_fantasia integer */
    public $nome_fantasia;
    /** @var $razao_social string */
    public $razao_social;

    public static function tableName()
    {
        return "usuarios";
    }

    public static function primaryKeyName()
    {
        return "id_usuario";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_usuario = $pk;
            return $this;
        }
        return $this->id_usuario;
    }

    public function behaviors()
    {
        return [
            'senha' => function () {
                if ($this->senha != "") {
                    return md5($this->senha);
                }
                $this->unload('senha');
                $this->senha = null;
            },
            'repeat' => function () {
                if ($this->repeat != "") {
                    return md5($this->repeat);
                }
                return null;
            },
            'data_cadastro' => function () {
                if (Admin::isInsert()) {
                    return (new Date(time()));
                }
            },
            'session_namespace' => function () {
                if ($this->tipo == 0) {
                    return 'ADM';
                } else {
                    return 'FUN';
                }
            },
            'status' => function () {
                if ($this->loaded('status') === false) {
                    return 0;
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

    public function rules()
    {
        return [
            'email' => [
                'required' => true,
                'email' => true,
                'db' => function () {
                    if (Session::get('action') == 'insert') {
                        $model = (new Usuarios())
                            ->select()
                            ->from(['USUARIOS' => 'usuarios'])
                            ->where(DbDict::EQUAL, ['USUARIOS.email' => $this->email])
                            ->one();
                    } else {
                        $model = (new Usuarios())
                            ->select()
                            ->from(['USUARIOS' => 'usuarios'])
                            ->where(DbDict::EQUAL, ['USUARIOS.email' => $this->email])
                            ->andWhere(DbDict::DIFFERENT, ['USUARIOS.' . Usuarios::primaryKeyName() => $this->primaryKey()])
                            ->one();
                    }
                    if ($model) {
                        return false;
                    }
                }
            ],
            'tipo' => [
                'required' => true,
            ],
            'repeat' => [
                'safe'
            ],
            'senha' => [
                'required' => function () {
                    if (Session::get('action') == 'insert') {
                        return true;
                    }
                    return false;
                },
                'equals' => [
                    function ($senha, $comp) {
                        if (Session::get('action') == 'insert' || strlen($this->senha) > 0 || strlen($this->repeat) > 0) {
                            return Validator::equals($senha, $comp);
                        } else {
                            $this->unload('senha');
                            $this->unload('repaet');
                        }
                        return true;
                    },
                    [
                        $this->repeat
                    ]
                ],
                'length' => [
                    function ($senha, $length) {
                        if (Session::get('action') == 'insert' || strlen($this->senha) > 0 || strlen($this->repeat) > 0) {
                            return Validator::lengthLargerOrEqual($this->getOldValue('senha'), $length);
                        } else {
                            $this->unload('senha');
                            $this->unload('repaet');
                        }
                        return true;
                    },
                    [
                        6
                    ]
                ]
            ],
        ];
    }

    public function messages()
    {
        return [
            'email' => [
                'required' => 'Esse campo é obrigatório',
                'email' => 'Insira um e-mail válido',
                'db' => 'Outro usuário está usando esté e-mail',
            ],
            'tipo' => [
                'required' => 'Escolha uma opção',
            ],
            'senha' => [
                'required' => 'Esse campo é obrigatório',
                'equals' => 'Esse campo deve ser igual ao campo repita a senha.',
                'length' => 'Esse campo deve ter no minimo 6 caracteres',
            ]
        ];
    }

    public function letsTry($id, $limite)
    {
        return [
            'status' => true,
            'content' => [
                'id' => $id,
                'limit' => $limite
            ]
        ];
    }

    public function labels()
    {
        return [
            'nome' => 'Nome: ',
            'email' => 'E-mail: ',
            'senha' => 'Senha: ',
            'tipo' => 'Tipo do usuário: ',
            'repeat' => 'Repita a senha: ',
            'status' => 'Ativo / Inativo: ',
            'telefone' => 'Telefone: ',
            'celular' => 'Celular: ',
            'cpf' => 'CPF (se for pessoa física): ',
            'cnpj' => 'CNPJ (se for pessoa jurídica): ',
            'cep' => 'CEP: ',
            'cidade' => 'Cidade: ',
            'estado' => 'Estado: ',
            'rua' => 'Rua: ',
            'numero' => 'Número: ',
            'bairro' => 'Bairro: ',
            'complemento' => 'Complemento: ',
            'razao_social' => 'Razão social (somente pessoa jurídica): ',
            'nome_fantasia' => 'Nome fantasia (somente pessoa jurídica): '
        ];
    }
}