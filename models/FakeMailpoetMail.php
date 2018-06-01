<?php class FakeMailpoetMail extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_mail integer */
    public $id_mail;

    /** @var $id_lista integer */
    public $id_lista;

    /** @var $titulo string */
    public $titulo;

    /** @var $imagem string */
    public $imagem;

    /** @var $html string */
    public $html;

    /** @var $clicks integer */
    public $clicks;

    /** @var $linkto string */
    public $linkto;

    /** @var $complete string */
    public $complete;

    public static function tableName()
    {
        return "fake_mailpoet_mail";
    }

    public static function primaryKeyName()
    {
        return "id_mail";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_mail = $pk;
            return $this;
        }
        return $this->id_mail;
    }

    public function behaviors()
    {
        return [
            'complete' => function(){
                return htmlentities($this->complete);
            }
        ];
    }

    public function reverseBehaviors()
    {
        return [
            'complete' => function(){
                return html_entity_decode($this->complete);
            }
        ];
    }

    public function labels()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }
}