<?php

class FakeMailpoetGrupo extends Model

{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_grupo integer */
    public $id_grupo;
    /** @var $nome string */
    public $nome;

    /** @var $descricao string */
    public $descricao;

    /** @var $pessoas [] */
    public $pessoas = [];

    public static function tableName()
    {
        return "fake_mailpoet_grupo";
    }

    public static function primaryKeyName()
    {
        return "id_grupo";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_grupo = $pk;
            return $this;
        }
        return $this->id_grupo;
    }

    public function behaviors()
    {
        return [];
    }

    public function reverseBehaviors()
    {
        return [];
    }

    public function labels()
    {
        return [
            'nome' => 'Nome: ',
            'descricao' => 'Descrição: '
        ];
    }

    public function rules()
    {
        return [
            'pessoas' => [
                'safe' => 'safe'
            ]
        ];
    }
}