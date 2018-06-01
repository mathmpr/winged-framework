<?php

class Contatos extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_contato integer */
    public $id_contato;

    /** @var $nome integer */
    public $nome;

    /** @var $email integer */
    public $email;

    /** @var $telefone string */
    public $telefone;

    /** @var $mensagem string */
    public $mensagem;

    /** @var $data_contato string */
    public $data_contato;

    public static function tableName()
    {
        return "contatos";
    }

    public static function primaryKeyName()
    {
        return "id_contato";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_contato = $pk;
            return $this;
        }
        return $this->id_contato;
    }
}