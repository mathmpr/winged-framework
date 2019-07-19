<?php

namespace Winged\Model;

use Winged\Http\Session;
use Winged\Date\Date;
use Winged\Database\DbDict;
use Winged\Validator\Validator;

class Usuarios extends Model
{
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

    /** @var $repeat string */
    public $repeat;

    /** @var $warn_days string */
    public $warn_days;

    /** @var $telefone string */
    public $telefone;

    /** @var $change_pass_please string */
    public $change_pass_please;

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
                if (Session::get('action') == 'insert') {
                    return Date::now()->sql();
                }
            },
            'tipo' => function () {
                if ($this->loaded('tipo') === false && Login::currentIsAdm()) {
                    return 1;
                } elseif (!Login::currentIsAdm()) {
                    return 1;
                } elseif ($this->id_usuario != Login::current()->id_usuario && $this->loaded('tipo')) {
                    return $this->tipo;
                }
                return 0;
            },
            'session_namespace' => function () {
                if ($this->tipo == 0) {
                    return 'ADM';
                } else {
                    return 'FUN';
                }
            },
            'status' => function () {
                if ($this->loaded('status') === false && Login::currentIsAdm()) {
                    return 0;
                }
            },
            'warn_days' => function () {
                if ($this->loaded('warn_days') === false) {
                    return 5;
                }
            }
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
                            ->where(ELOQUENT_EQUAL, ['USUARIOS.email' => $this->email])
                            ->one();
                    } else {
                        $model = (new Usuarios())
                            ->select()
                            ->from(['USUARIOS' => 'usuarios'])
                            ->where(ELOQUENT_EQUAL, ['USUARIOS.email' => $this->email])
                            ->andWhere(ELOQUENT_DIFFERENT, ['USUARIOS.' . Usuarios::primaryKeyName() => $this->primaryKey()])
                            ->one();
                    }
                    if ($model) {
                        return false;
                    }
                }
            ],
            'tipo' => [
                'required' => function () {
                    if (Login::currentIsAdm() && !$this->loaded('tipo')) {
                        return true;
                    }
                    return false;
                },
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
                            return (new Validator())->equals($senha, $comp);
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
                            return (new Validator())->lengthLargerOrEqual($this->getOldValue('senha'), $length);
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

    public function labels()
    {
        return [
            'nome' => 'Nome: ',
            'email' => 'E-mail: ',
            'senha' => 'Senha: ',
            'tipo' => 'Tipo do usuário: ',
            'repeat' => 'Repita a senha: ',
            'status' => 'Ativo / Inativo: ',
            'warn_days' => 'Buscar próximos pedidos agendados em quantos dias? ',
            'telefone' => 'Telefone: ',
        ];
    }
}