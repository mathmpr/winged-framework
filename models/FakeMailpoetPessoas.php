<?php class FakeMailpoetPessoas extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_pessoa integer */
    public $id_pessoa;
    /** @var $nome string */
    public $nome;
    /** @var $email string */
    public $email;
    /** @var $recipe integer */
    public $recipe;
    /** @var $cancelado integer */
    public $cancelado;

    public static function tableName()
    {
        return "fake_mailpoet_pessoas";
    }

    public static function primaryKeyName()
    {
        return "id_pessoa";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_pessoa = $pk;
            return $this;
        }
        return $this->id_pessoa;
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
            'nome' => 'Nome',
            'email' => 'E-mail'
        ];
    }

    public function rules()
    {
        return [
            'email' => [
                'required' => true,
                'email' => true
            ],
            'nome' => [
                'required' => true,
            ]
        ];
    }

    public function messages()
    {
        return [
            'email' => [
                'required' => 'Este campo é obrigatório.',
                'email' => 'Esté campo precisa conter um e-mail válido.'
            ],
            'nome' => [
                'required' => 'Este campo é obrigatório.',
            ]
        ];
    }
}